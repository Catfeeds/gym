var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    clocklist: [], // 打卡列表
    pageNum: 0, // 页码
  },

  onLoad: function() {
    this.getClocklist();
  },

  /**
   * 获取打卡列表
   */
  getClocklist: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: 'true'
    })
    util.post('clock/getClocklist', {
      uid: app.globalData.uid,
      userType: app.globalData.userType,
      pageNum: that.data.pageNum
    }).then(res => {
      if (res) {
        that.setData({
          clocklist: that.data.clocklist.concat(res || []),
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
   * 通过lat和lng 打开地图
   */
  showLocation: function(evt) {
    let location = evt.currentTarget.dataset.location;
    if (!location) {
      util.modalPromisified({
        title: '系统提示',
        content: '打卡地点为空',
        showCancel: false
      })
      return;
    }
    location = location.split(',');
    wx.openLocation({
      latitude: parseFloat(location[0]),
      longitude: parseFloat(location[1]),
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    this.setData({
      clocklist: [],
      pageNum: 0,
    })
    this.getClocklist();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    this.getClocklist();
  }

})