const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    storeInfo: '', // 门店详情
  },

  onLoad: function(options) {
    this.getStoreInfo();
  },

  /**
   * 获取门店信息
   */
  getStoreInfo: function() {
    var that = this;
    util.post('minibase/getStoreInfo', {
      openid: wx.getStorageSync('openid')
    }).then(res => {
      that.setData({
        storeInfo: res || ""
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络后重试',
        confirmText: '重新连接'
      }).then(res => {
        if (res.confirm) {
          that.getStoreInfo();
        }
      })
    })
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || '这样子的美术课程你一定很喜欢~',
      path: '/pages/index/index'
    }
  },

  /**
   * 给店家拨打电话资讯
   */
  callStoreService: function() {
    util.modalPromisified({
      title: '系统提示',
      content: '网络错误，请检查网络后重试',
      confirmText: '重新连接'
    }).then(res => {
      if (res.cancel) return;
      let servicePhone = app.globalData.setting.service_phone;
      if (res.confirm && servicePhone) {
        wx.makePhoneCall({
          phoneNumber: servicePhone,
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '暂时未设置电话',
          showCancel: false
        })
      }
    })
  }

})