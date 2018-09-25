const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    isHaveClass: true,
    classIdx: 0, // 当前选择的班级下标
    clockTypePicker: ['正常打卡', '迟到打卡', '旷课打卡'], // 打卡类型选择
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this.getTearcherClass();
  },

  /**
   * 获取该教师所带相关班级的数据
   */
  getTearcherClass: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    util.post('teacher/getTearcherClass', {
      tid: app.globalData.uid
    }, 300).then(res => {
      if (res) {
        // 构造班级的picker
        let classPicker = [];
        classPicker.push('请选择班级');
        for (let i = 0; i < res.length; i++) {
          classPicker.push(res[i].class_name + ' - ' + res[i].class_time);
        }
        that.setData({
          classList: res,
          classPicker: classPicker
        })
      } else {
        that.setData({
          isHaveClass: false
        })
      }
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络连接后重试',
        confirmText: '重试',
        cancelText: '退出'
      }).then(res => {
        if (res.confirm) that.getTearcherClass();
        if (res.cancel) wx.navigateBack({
          delta: 1
        })
      })
    })
  },

  /**
   * 教师选择班级
   */
  chooseClass: function(evt) {
    this.setData({
      classIdx: evt.detail.value
    })
    if (evt.detail.value != 0) {
      this.getClassStudent();
    } else {
      this.setData({
        notClockList: []
      })
    }
  },

  /**
   * 教师选择对应的打卡类型
   */
  chooseClockType: function(evt) {
    var that = this;
    let idx = evt.currentTarget.dataset.idx,
      notClockList = that.data.notClockList;
    notClockList[idx].clockType = evt.detail.value;
    that.setData({
      notClockList: notClockList
    })
  },

  /**
   * 获取班级对应的学生
   */
  getClassStudent: function() {
    var that = this;
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    util.post('teacher/getClassStudent', {
      tid: app.globalData.uid,
      classid: that.data.classList[that.data.classIdx - 1].class_id,
      ischeck: 1
    }, 300).then(res => {
      that.setData({
        notClockList: res.not_clock,
        alreadyClockList: res.already_clock
      })
    }).catch(res => {
      if (res.data.code == 402) {
        util.modalPromisified({
          title: '系统提示',
          content: '当前班级暂无学生，请重新选择',
          showCancel: false
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误，请检查网络后重试',
          showCancel: false
        })
      }
    })
  },

  /**
   * 教师选择打卡的学生
   */
  chooseStudent: function(evt) {
    var that = this;
    let notClockList = that.data.notClockList,
      idx = evt.currentTarget.dataset.idx;
    notClockList[idx].select = !notClockList[idx].select;
    that.setData({
      notClockList: notClockList
    })
  },

  /**
   * 教师提交打卡
   */
  submitClock: function() {
    var that = this;
    if (!that.isCanSubmit()) return;

    util.modalPromisified({
      title: '系统提示',
      content: '确定要提交本次打卡吗？'
    }).then(res => {
      if (res.cancel) return;
      wx.showLoading({
        title: '打卡中',
        mask: true
      })
      // 构造参与打卡的学生
      let uids = "",
        notClockList = that.data.notClockList,
        classId = that.data.classList[that.data.classIdx - 1].class_id;
      for (let i = 0; i < notClockList.length; i++) {
        if (notClockList[i].select) uids += notClockList[i].uid + '*' + notClockList[i].clockType + '#';
      }
      util.post('teacher/submitClock', {
        tid: app.globalData.uid, // 教师ID
        stuids: uids,
        timestamp: parseInt(Date.now() / 1000),
        classid: classId
      }).then(res => {
        wx.showToast({
          title: '打卡成功',
          duration: 1000
        })
        that.setData({
          isShowFailedReason: false
        })
        // 打卡成功 刷新数据
        setTimeout(res => {
          that.getClassStudent();
        }, 1000)
      }).catch(res => {
        if (res.data.code == 403) {
          wx.showToast({
            title: '打卡失败',
            icon: 'none',
            duration: 800
          })
          // 构造用户username
          let failedClockUser = res.data.data;
          for (let i = 0; i < failedClockUser.length; i++) {
            for (let j = 0; j < notClockList.length; j++) {
              if (failedClockUser[i].uid == notClockList[j].uid) {
                failedClockUser[i].username = notClockList[j].username;
                break;
              }
            }
          }
          that.setData({
            isShowFailedReason: true,
            failedClockUser: failedClockUser
          })
        } else if (res.data.code == 401) {
          wx.showToast({
            title: '打卡失败',
            icon: 'none',
            duration: 800
          })
        } else {
          util.modalPromisified({
            title: '系统提示',
            content: '网络错误，请检查网络后重试',
            showCancel: false
          })
        }
      })
    })
  },

  /**
   * 检测教师是能能够提交打卡记录
   */
  isCanSubmit: function() {
    var that = this;
    // 班级选择校验
    if (that.data.classIdx == 0) {
      util.modalPromisified({
        title: '系统提示',
        content: '请选择班级',
        showCancel: false
      })
      return false;
    }
    // 学生选择校验
    let notClockList = that.data.notClockList;
    let isChooseStudent = false;
    for (let i = 0; i < notClockList.length; i++) {
      if (notClockList[i].select) {
        isChooseStudent = true;
        break;
      }
    }
    if (!isChooseStudent) {
      util.modalPromisified({
        title: '系统提示',
        content: '请至少选择一名参与此次打卡的学生',
        showCancel: false
      })
      return false;
    }
    // 都没问题
    return true;
  }

})