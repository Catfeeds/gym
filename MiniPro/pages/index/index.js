const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    banner: [{
      img: '/images/banner.png'
    }], // 默认banner
    isCoach: false, // 用户身份是否为教练
    projectList: [], // 项目列表
  },

  onLoad: function() {
    this.getIndex();
  },

  /**
   * 获取首页界面详情 主要是获取banner
   */
  getIndex: function() {
    var that = this;
    // 先进行登陆
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    app.loadInfo().then(res => {
      // 先校验身份
      console.log(app.globalData)
      if (!app.globalData.isAuth) {
        util.modalPromisified({
          title: '系统提示',
          content: '您需要先绑定用户身份才可进行后续操作',
          confirmText: '进行绑定',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/userauth/userauth'
          })
        })
      } else {
        return util.post('minibase/getIndex', {
          uid: app.globalData.uid
        }, 100)
      }
    }).then(res => {
      let banner = that.data.banner;
      that.setData({
        banner: res || banner,
        isCoach: app.globalData.userType == 1 ? true : false
      })
      // 根据系统设置 修改小程序名称
      if (app.globalData.setting) {
        wx.setNavigationBarTitle({
          title: app.globalData.setting.mini_name,
        })
      }
    }).catch(res => {
      if (res.data.code == 403) {
        util.modalPromisified({
          title: '系统提示',
          content: '您已被禁止进入该小程序，请及时联系管理员',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/nouser/nouser',
          })
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误，请尝试检查网络后重试',
          confirmText: '重新连接'
        }).then(res => {
          if (res.confirm) {
            that.getIndex();
          }
        })
      }
    })
  },

  /**
   * 跳转到通用展示界面
   */
  navToCommonshow: function(){
    wx.navigateTo({
      url: '/pages/commonshow/commonshow?scene=1',
    })
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return {
      title: app.globalData.setting.share_text || 'Reshape带你重塑形体~',
      path: '/pages/index/index'
    }
  },

})