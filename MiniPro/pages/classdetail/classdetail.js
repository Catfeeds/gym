var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    stuList: [], // 班级列表
  },

  onLoad: function(options) {
    if (!options.classid) {
      util.modalPromisified({
        title: '系统提示',
        content: '参数错误，请联系开发者',
        confirmText: '退出',
        showCancel: false
      }).then(res => {
        wx.navigateBack({
          delta: 1
        })
      })
      return;
    }
    this.getClassStuList(options.classid);
  },

  /**
   * 获取该教师所带相关班级的数据
   */
  getClassStuList: function(classid) {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    util.post('teacher/getClassStudent', {
      tid: app.globalData.uid,
      classid: classid,
      isdetail: 1 // 是否获取班级更多详细信息
    }, 300).then(res => {
      if (!res) return;
      that.setData({
        stuList: res
      })
    }).catch(res => {
      if (res.data.code == 402) {
        util.modalPromisified({
          title: '系统提示',
          content: '当前班级暂无学生，请及时联系管理员',
          showCancel: false
        }).then(res => {
          wx.navigateBack({
            delta: 1
          })
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误，请检查网络连接后重试',
          confirmText: '重试',
          cancelText: '退出'
        }).then(res => {
          if (res.confirm) that.getClassStuList();
          if (res.cancel) wx.navigateBack({
            delta: 1
          })
        })
      }
    })
  },

  /**
   * 向学生家长拨打电话
   */
  callStuParent: function(evt){
    util.modalPromisified({
      title: '系统提示',
      content: '您确定要向该家长拨打电话吗？'
    }).then(res => {
      if (res.cancel) return;
      wx.makePhoneCall({
        phoneNumber: evt.currentTarget.dataset.phone,
      })
    })
  },


})