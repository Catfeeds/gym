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
      // uid: app.globalData.uid,
      uid: 1,
      // userType: app.globalData.userType,
      userType: 1,
      pageNum: that.data.pageNum
    }).then(res => {
      that.setData({
        clocklist: that.data.clocklist.concat(res),
        pageNum: that.data.pageNum + 1
      })
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
   * 通过lat和lng 打开地图
   */
  showLocation: function(evt) {
    let location = evt.currentTarget.dataset.location;
    location = location.split(',');
    wx.openLocation({
      latitude: location[0],
      longitude: location[1],
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