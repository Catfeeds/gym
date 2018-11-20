App({

  onLaunch: function() {
    var that = this;

    // 更新检测
    const updateManager = wx.getUpdateManager()
    updateManager.onCheckForUpdate(function(res) {
      // 请求完新版本信息的回调
      // console.log(res.hasUpdate)
    })
    updateManager.onUpdateReady(function() {
      util.modalPromisified({
        title: '更新提示',
        content: '新版本已经准备好，请点击确认以更新',
        showCancel: false,
      }).then(res => {
        updateManager.applyUpdate()
      })
    })

    that.loadInfo();
  },

  /**
   * 用户登陆
   * 装载系统设置
   */
  loadInfo: function() {
    var that = this;
    let util = require('/utils/util.js');
    // 用户信息请求
    util.loginPromisified().then(res => {
      if (res.code) {
        return util.post('minibase/getUserAccount', {
          openid: wx.getStorageSync('openid') || '',
          code: res.code
        }, 100)
      }
    }).then(res => {
      // 先校验身份
      wx.setStorageSync('openid', res.openid);
      that.globalData.uid = res.uid;
      that.globalData.isAuth = res.isAuth || false;
      // 1 会员 2 教练
      that.globalData.userType = res.user_type;
      if (!res.isAuth) {
        util.modalPromisified({
          title: '系统提示',
          content: '您需要先进行认证才可进行后续操作',
          confirmText: '进行认证',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/userauth/userauth'
          })
        })
      } else {
        return util.post('minibase/getSystemSetting', {
          openid: res.openid
        }, 100)
      }
    }).then(res => {
      console.log(res)
      that.globalData.setting = res || [];
    }).catch(res => {
      console.log(res)
    })
  },

  /**
   * 全局变量
   */
  globalData: {
    siteroot: 'https://test.kekexunxun.com/mini/'
  }
})