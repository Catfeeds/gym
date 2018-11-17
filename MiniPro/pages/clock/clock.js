const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    timeStr: '', // 时间
    clockCount: 0, // 用户累计打卡时间
    nonStopClockCount: 0, // 用户连续打卡时间
    showClockList: false, // 是否展示用户课程
    selectedCourseIdx: -1, // 选中的课程
    isLocationAuth: false, // 用户地址授权判断
    clockText: '开始打卡',
    isHaveUnFinish: false, // 是否有未打卡完成的课程
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
      // uid: 1,
      userType: app.globalData.userType
      // userType: 1
    }, 500).then(res => {
      that.setData({
        clockCount: res.clockInfo.clockCount,
        nonStopClockCount: res.clockInfo.nonStopClockCount,
        courseList: res.course || [],
        isHaveUnFinish: res.clockInfo.isHaveUnfinishClock ? true : false,
        unfinishClock: res.clockInfo.unfinishClock || [],
        clockText: res.clockInfo.isHaveUnfinishClock ? '结束打卡' : '开始打卡'
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
   * 用户打卡
   * 当用户未选择课程的时候跳转到选择课程的界面
   * 如果用户选择完课程就直接提示用户打卡
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
        fail: function(res) {
          console.log(res)
        }
      })
      return;
    }
    let lat = "",
      lng = '';
    util.locationPromisified().then(res => {
      lat = res.latitude;
      lng = res.longitude;
      // 已选择课程
      util.modalPromisified({
        title: '系统提示',
        content: '您确认要进行打卡操作吗？'
      }).then(res => {
        if (res.cancel) return;
        wx.showLoading({
          title: '打卡中',
          mask: true
        })
        util.post('clock/startClock', {
          // uid: app.globalData.uid,
          uid: 1,
          // userType: app.globalData.userType,
          userType: 1,
          courseId: that.data.courseList[that.data.selectedCourseIdx].course_id,
          timeStamp: parseInt(Date.now() / 1000),
          location: lat + ',' + lng
        }, 500).then(res => {
          wx.showToast({
            title: '打卡成功',
            duration: 1200
          })
        }).catch(res => {
          wx.showToast({
            title: '打卡失败',
            icon: 'loading',
            duration: 1200
          })
        })
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '系统错误，请重启小程序或检查微信后重试',
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
      content: '您确定要结束当前打卡吗？',
    }).then(res => {
      if (res.cancel) return;
      util.locationPromisified().then(res => {
        lat = res.latitude;
        lng = res.longitude;
        util.post('clock/finishClock', {
          // uid: app.globalData.uid,
          uid: 1,
          // userType: app.globalData.userType
          userType: 1,
          clockId: that.data.unfinishClock.clock_id,
          courseId: that.data.unfinishClock.course_id,
          timeStamp: parseInt(Date.now() / 1000),
          location: lat + ',' + lng
        }, 500).then(res => {
          wx.showToast({
            title: '打卡成功',
            duration: 1200
          })
        }).catch(res => {
          wx.showToast({
            title: '打卡失败',
            icon: 'loading',
            duration: 1200
          })
        })
      })
    })
  },

  /**
   * 切换到课程选择界面
   */
  navToCourseChoose: function() {
    // 如果没有课程 就要提示用户先参加课程
    if (this.data.courseList.length == 0) {
      util.modalPromisified({
        title: '系统提示',
        content: '您还未参加任何课程，无法打卡',
        showCancel: false
      })
      return;
    }
    this.setData({
      showClockList: true
    })
    // 将tabbar隐藏
    wx.hideTabBar({
      animation: true
    })
  },

  /**
   * 选择课程
   */
  chooseCourse: function(evt) {
    var that = this;
    let courseList = that.data.courseList,
      idx = evt.currentTarget.dataset.idx;
    // 1 判断当前点击是否为选中状态
    if (courseList[idx].isChecked) {
      courseList[idx].isChecked = false;
    } else {
      // 将所有的item的check状态重置
      for (let i = 0; i < courseList.length; i++) {
        courseList[i].isChecked = false;
      }
      // 设置选中的item
      courseList[idx].isChecked = true;
    }
    that.setData({
      courseList: courseList,
      selectedCourseIdx: idx
    })
  },

  /**
   * 确认选择当前课程
   */
  confirmCourse: function() {
    var that = this;
    // 如果没有选择课程 要提示用户选择
    if (that.data.selectedCourseIdx == -1) {
      util.modalPromisified({
        title: '系统提示',
        content: '请先选择课程',
        showCancel: false
      })
      return;
    }
    util.modalPromisified({
      title: '系统提示',
      content: '您是否确认选择当前课程'
    }).then(res => {
      if (res.cancel) return;
      // 确认选择
      that.setData({
        showClockList: false
      })
      // 将tabbar展示出来
      wx.showTabBar({
        aniamtion: true
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
      title: app.globalData.setting.share_text || 'Reshape带你重塑形体~',
      path: '/pages/index/index'
    }
  }
})