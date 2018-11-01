var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    feedbackList: [],
    pageNum: 0
  },

  onShow: function() {
    this.setData({
      pageNum: 0
    })
    this.getUserFeedback();
  },

  /**
   * 获取用户的反馈列表
   */
  getUserFeedback: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    util.post('user/getUserFeedback', {
      uid: app.globalData.uid,
      pageNum: that.data.pageNum,
      userType: app.globalData.userType
    }).then(res => {
      if (!res) {
        if (that.data.pageNum != 0) {
          wx.showToast({
            title: '没有更多了',
            icon: 'loading',
            duration: 1000
          })
        }
        return;
      }
      that.setData({
        feedbackList: that.data.feedbackList.concat(res),
        pageNum: that.data.pageNum + 1
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络后重试',
        showCancel: false
      })
    }).finally(res => {})
  },

  /**
   * 跳转到发布意见反馈的界面
   */
  navToNewFeedback: function() {
    wx.navigateTo({
      url: '/pages/sendfeedback/sendfeedback'
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    this.setData({
      feedbackList: [],
      pageNum: 0
    })
    this.getUserFeedback();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    this.getUserFeedback();
  },

  /**
   * 图片预览
   */
  previewImage: function(evt){
    wx.previewImage({
      urls: [evt.currentTarget.dataset.img],
    })
  }

})