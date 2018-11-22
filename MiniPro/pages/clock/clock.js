const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    timeStr: '', // 时间
    clockCount: 0, // 用户累计打卡时间
    nonStopClockCount: 0, // 用户连续打卡时间
    isLocationAuth: false, // 用户地址授权判断
    clockText: '上班打卡',
    hasUnFinish: false, // 是否有未打卡完成的课程
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
   * 检测用户是否有进项地址授权
   */
  onShow: function() {
    var that = this;
    util.settingPromisified().then(res => {
      if (res.authSetting['scope.userLocation']) {
        that.setData({
          isLocationAuth: true,
          userType: app.globalData.userType || 1
        })
      }
    })
  },

  /**
   * 获取用户打卡信息
   */
  getUserClockInfo: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    util.post('user/getClockInfo', {
      uid: app.globalData.uid,
      userType: app.globalData.userType
    }, 500).then(res => {
      that.setData({
        clockCount: res.clockInfo.clockCount,
        nonStopClockCount: res.clockInfo.nonStopClockCount,
        hasUnFinish: res.clockInfo.unFinishClock ? true : false,
        unFinishClock: res.clockInfo.unFinishClock || [],
        clockText: res.clockInfo.unFinishClock ? '下班打卡' : '上班打卡',
        workTime: res.clockInfo.coach_work_time || ""
      })
    }).catch(error => {
      console.log(res)
      util.modalPromisified({
        title: '系统提示',
        content: error.toString(),
        showCancel: false
      })
    })
  },

  /**
   * 当前时间展示
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
   * 教练打卡
   * 
   * 用户的地址授权问题在认证时就应该先行获取 不需要等到现在才获取
   */
  startClock: function() {
    var that = this;
    // 判断用户是否有进行地址授权
    if (!that.data.isLocationAuth) {
      wx.openSetting({
        success: res => {
          if (res.authSetting['scope.userLocation']) {
            util.modalPromisified({
              title: '系统提示',
              content: '授权成功，可以进行打卡操作',
              showCancel: false
            })
            that.setData({
              isLocationAuth: true
            })
          } else {
            util.modalPromisified({
              title: '系统提示',
              content: '授权取消，打卡失败',
              showCancel: false
            })
          }
        },
        fail: function(error) {
          util.modalPromisified({
            title: '系统错误',
            content: error.toString(),
            showCancel: false
          })
        }
      })
      return;
    }
    // 判断是否到达打卡时间
    var curTime = parseInt(Date.now() / 1000);
    let workTime = that.data.workTime;
    if (workTime && workTime.length > 0) {
      if (curTime < workTime.coach_work_start_at) {
        util.modalPromisified({
          title: '系统提示',
          content: '未到打卡时间，打卡时间为' + workTime.coach_work_start_at_conv + ' - ' + workTime.coach_work_end_at_conv,
          showCancel: false
        })
        return;
      }
    }
    let lat = "",
      lng = '';
    util.locationPromisified().then(res => {
      lat = res.latitude;
      lng = res.longitude;
      util.modalPromisified({
        title: '系统提示',
        content: '确认要开始上班打卡吗？'
      }).then(res => {
        if (res.cancel) return;
        wx.showLoading({
          title: '打卡中',
          mask: true
        })
        util.post('clock/startClock', {
          uid: app.globalData.uid,
          userType: app.globalData.userType,
          timeStamp: curTime,
          location: lat + ',' + lng
        }, 500).then(res => {
          wx.showToast({
            title: '打卡成功',
            duration: 1200
          })
          setTimeout(function() {
            that.getUserClockInfo();
          }, 1200)
        }).catch(res => {
          if (res.data.code == 405) {
            util.modalPromisified({
              title: '系统提示',
              content: '今日已经打过卡啦，不能重复打卡',
              showCancel: false
            })
          } else if (res.data.code == 401) {
            util.modalPromisified({
              title: '系统提示',
              content: '打卡失败，打卡超时',
              showCancel: false
            })
          } else if (res.data.code == 402) {
            util.modalPromisified({
              title: '系统提示',
              content: '打卡失败，不在打卡时间范围',
              showCancel: false
            })
          } else {
            wx.showToast({
              title: '打卡失败',
              icon: 'loading',
              duration: 1200
            })
          }
        })
      })
    }).catch(error => {
      util.modalPromisified({
        title: '系统错误',
        content: error.toString(),
        showCancel: false
      })
    })
  },

  /**
   * 用户结束打卡
   */
  endClock: function() {
    var that = this;
    util.modalPromisified({
      title: '系统提示',
      content: '确定要进行下班打卡吗？',
    }).then(res => {
      if (res.cancel) return;
      // 下班时间判断
      var curTime = parseInt(Date.now() / 1000);
      let workTime = that.data.workTime;
      if (workTime) {
        console.log(curTime)
        console.log(workTime.coach_work_end_at)
        console.log(curTime < workTime.coach_work_end_at)
        if (curTime < workTime.coach_work_end_at) {
          util.modalPromisified({
            title: '系统提示',
            content: '未到打卡时间，打卡时间为' + workTime.coach_work_start_at_conv + ' - ' + workTime.coach_work_end_at_conv,
            showCancel: false
          })
          return;
        }
      }
      util.locationPromisified().then(res => {
        lat = res.latitude;
        lng = res.longitude;
        util.post('clock/finishClock', {
          clockId: that.data.unFinishClock.clock_id,
          timeStamp: curTime,
          location: lat + ',' + lng
        }, 500).then(res => {
          wx.showToast({
            title: '打卡成功',
            duration: 1200
          })
          setTimeout(function() {
            that.getUserClockInfo();
          }, 1200)
        }).catch(error => {
          util.modalPromisified({
            title: '系统错误',
            content: '打卡失败，未到下班时间',
            showCancel: false
          })
        })
      })
    })
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
      title: app.globalData.setting.share_text || 'Reshape重塑形体~',
      path: '/pages/index/index'
    }
  }
})