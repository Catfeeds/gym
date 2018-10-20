const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    isLogin: false, // 用户是否登陆
  },

  onLoad: function(options) {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 用户个人信息查看与修改
   */
  myInfo: function() {
    wx.navigateTo({
      url: '/pages/userinfo/userinfo',
    })
  },

  /**
   * 跳转到打卡列表
   */
  navToClocklist: function() {
    wx.navigateTo({
      url: '/pages/clocklist/clocklist',
    })
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || 'Reshape带你重塑身形~',
      path: '/pages/index/index'
    }
  },

  /**
   * 跳转到关于我们界面
   */
  navToAboutUs: function() {
    wx.navigateTo({
      url: '/pages/commonshow/commonshow?scene=1',
    })
  },

  /**
   * 跳转到我的反馈
   */
  navToFeedback: function(evt) {
    wx.navigateTo({
      url: '../feedback/feedback',
    })
  },

  /**
   * 微信地图导航
   */
  navToLocation: function(evt) {
    // 获取位置坐标
    let location = app.globalData.setting.location;
    let locationArr = location.split(',');
    wx.openLocation({
      latitude: parseFloat(locationArr[0]),
      longitude: parseFloat(locationArr[1]),
      fail: function() {
        util.modalPromisified({
          title: '系统提示',
          content: '坐标位置错误，请及时联系管理员',
          showCancel: false
        })
      }
    })
  },

  /**
   * 拨打客服电话
   */
  phoneCall: function() {
    // 获取客服电话
    let servicePhone = app.globalData.setting.service_phone;
    if (!servicePhone) {
      util.modalPromisified({
        title: '系统提示',
        content: '暂未设置客服电话，请使用微信客服功能',
        showCancel: false
      })
      return;
    }
    wx.makePhoneCall({
      phoneNumber: servicePhone
    })
  }

})