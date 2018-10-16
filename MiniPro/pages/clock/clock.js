const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    timeStr: '', // 时间
    clockCount: 0, // 用户累计打卡时间
    nonStopClockCount: 0, // 用户连续打卡时间
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
    this.getUserClockInfo();
  },

  /**
   * 获取用户打卡信息
   */
  getUserClockInfo: function() {
    var that = this;
    util.post('user/getClockInfo', {
      uid: app.globalData.uid,
      userType: app.globalData.userType
    }, 100).then(res => {
      that.setData({
        clockCount: res.clockCount,
        nonStopClockCount: res.nonStopClockCount
      })
    }).catch(res => {
      console.log(res)
      util.modalPromisified({
        title: '系统提示',
        content: '系统错误，请及时联系管理员',
        showCancel: false
      })
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
    this.getUserClockInfo();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || 'Reshape带你重塑形体~',
      path: '/pages/index/index'
    }
  }
})