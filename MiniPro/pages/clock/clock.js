const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    timeStr: '', // 时间
  },

  onLoad: function(options) {
    this.timeCountdown()
    // 设置顶部导航栏颜色
    wx.setNavigationBarColor({
      frontColor: '#ffffff',
      backgroundColor: '#b99ad2',
      animation: {
        duration: 1000,
        timingFunc: 'easeInOut'
      },
      success: function(res) {},
      fail: function(res) {},
      complete: function(res) {},
    })
  },

  /**
   * 时间倒计时
   */
  timeCountdown: function() {
    let intval = setInterval(res => {
      let date = new Date();
      let hour = date.getHours(),
        min = date.getMinutes(),
        sec = date.getSeconds();
      hour = hour <= 9 ? '0' + hour : hour;
      min = min <= 9 ? '0' + min : min;
      sec = sec <= 9 ? '0' + sec : sec;
      let timeStr = hour + ':' + min + ':' + sec;
      this.setData({
        timeStr: timeStr
      })
    }, 1000)
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})