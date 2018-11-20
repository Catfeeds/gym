var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    courselist: [], // 打卡列表
    pageNum: 0, // 页码
  },

  onLoad: function() {
    this.getCourselist();
  },

  /**
   * 获取打卡列表
   */
  getCourselist: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: 'true'
    })
    util.post('user/getCourse', {
      uid: app.globalData.uid,
      pageNum: that.data.pageNum
    }).then(res => {
      if (res) {
        that.setData({
          courselist: that.data.courselist.concat(res || []),
          pageNum: that.data.pageNum + 1
        })
      } else if (that.data.pageNum != 0) {
        wx.showToast({
          title: '没有更多了',
          icon: 'loading',
          duration: 1200
        })
      }
    }).catch(res => {
      if (res.data.code == 401) {
        wx.showToast({
          title: '暂无数据',
          duration: 1200
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '系统错误，请及时联系管理员',
          showCancel: false
        })
      }
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    this.setData({
      courselist: [],
      pageNum: 0,
    })
    this.getCourselist();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    this.getCourselist();
  }

})