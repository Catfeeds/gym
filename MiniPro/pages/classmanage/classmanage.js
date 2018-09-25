var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    classList: [], // 班级列表
  },

  onLoad: function (options) {
    this.getClassList();
  },

  /**
   * 获取该教师所带相关班级的数据
   */
  getClassList: function () {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    util.post('teacher/getTearcherClass', {
      tid: app.globalData.uid,
      isdetail: 1 // 是否获取班级更多详细信息
    }, 300).then(res => {
      if(!res) return;
      that.setData({
        classList: res
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络连接后重试',
        confirmText: '重试',
        cancelText: '退出'
      }).then(res => {
        if (res.confirm) that.getClassList();
        if (res.cancel) wx.navigateBack({
          delta: 1
        })
      })
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    this.getClassList();
  },

  /**
   * 跳转到课程详情界面
   */
  navToClassDetail: function(evt){
    wx.navigateTo({
      url: '/pages/classdetail/classdetail?classid=' + evt.currentTarget.dataset.classid
    })
  },

})