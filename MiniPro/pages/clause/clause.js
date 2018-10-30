const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    nodes: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this.getClause();
  },

  /**
   * 获取用户协议
   */
  getClause: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    util.post('minibase/getCaluse', {
      uid: app.globalData.uid
    }).then(res => {
      that.setData({
        nodes: res || []
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '系统错误，请及时联系管理员',
        showCancel: false
      })
    }).finally(res => {})
  }

})