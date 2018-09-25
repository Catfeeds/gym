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

    // 用户登陆记录本地存储
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)
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
            code: res.code
          }, 100)
        }
      }).then(res => {
        wx.setStorageSync('openid', res.openid);
        that.globalData.uid = res.uid || res.tid;
        // that.globalData.uid = 1;
        that.globalData.isAuth = res.isAuth || false;
        // 0 学生家长 1 教师
        that.globalData.userType = res.userType;
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
    siteroot: 'https://art.up.maikoo.cn/mini/'
  }

})