//获取应用实例
const app = getApp();
var util = require('../../utils/util.js');

Page({
  data: {
    clockList: [], // 课程列表
    userInfo: {}, // 用户信息
    pageNum: 0, // 页码
  },

  onLoad: function() {
    // 获取打卡记录
    this.getClockList();
  },

  /**
   * 获取用户打卡记录
   */
  getClockList: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    util.post('user/getUserClock', {
      pageNum: that.data.pageNum,
      uid: app.globalData.uid
    }).then(res => {
      if (!res) return;
      that.setData({
        clockList: that.data.clockList.concat(res),
        pageNum: that.data.pageNum + 1
      })
    }).catch(res => {
      wx.showToast({
        title: '网络错误',
        icon: 'none',
        duration: 1000
      })
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.showNavigationBarLoading();
    setTimeout(function() {
      that.setData({
        pageNum: 0,
        clockList: []
      })
      that.getClockList()
    }, 600)
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.showNavigationBarLoading();
    setTimeout(function() {
      that.getClockList()
    }, 600)
  },

})