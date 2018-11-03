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
  },

  /**
   * 用户登陆
   * 装载系统设置
   */
  loadInfo: function() {
    var that = this;
    let util = require('/utils/util.js');
    let promise = new Promise((resolve, reject) => {
      //网络请求
      // 用户信息请求
      util.loginPromisified().then(res => {
        if (res.code) {
          return util.post('minibase/getUserAccount', {
            openid: wx.getStorageSync('openid'),
            // openid: "o2XWA4jqHsVsJoNgh0V6deLbeW9Y",
            code: res.code
          }, 100)
        }
      }).then(res => {
        wx.setStorageSync('openid', res.openid);
        that.globalData.uid = res.uid;
        that.globalData.isAuth = res.isAuth || false;
        // 1 会员 2 教练
        that.globalData.userType = res.user_type;
        return util.post('minibase/getSystemSetting', {
          openid: res.openid
        }, 100)
      }).then(res => {
        console.log(res)
        that.globalData.setting = res || [];
        resolve('yes');
      }).catch(res => {
        reject(res);
      })
    });
    return promise;
  },

  /**
   * 全局变量
   */
  globalData: {
    siteroot: 'https://test.kekexunxun.com/mini/'
  }

})