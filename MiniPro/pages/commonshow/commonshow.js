const app = getApp();
var util = require('../../utils/util.js');

// 通用的展示界面
// 关于我们 项目展示 Banner跳转
// scene = 1 关于我们 scene = 2 普通的项目展示

Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  onLoad: function(options) {
    if (options.scene == 1) {
      wx.setNavigationBarTitle({
        title: '关于我们',
      })
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})