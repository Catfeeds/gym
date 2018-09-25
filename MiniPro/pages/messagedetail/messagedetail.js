var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    msgInfo: [], // 消息详情
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    if (!options.msgid) {
      util.modalPromisified({
        title: '系统提示',
        content: '参数错误，请联系管理员',
        showCancel: false
      }).then(res => {
        wx.navigateBack({
          delta: 1
        })
      })
      return;
    }
    this.getMsgInfo(options.msgid);
  },

  /**
   * 获取消息详情
   */
  getMsgInfo: function(msgId) {
    var that = this;
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    util.post('message/getMsgInfo', {
      uid: app.globalData.uid,
      msgId: msgId
    }).then(res => {
      that.setData({
        msgInfo: res || []
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '该消息不存在或已被管理员删除',
        showCancel: false
      }).then(res => {
        wx.navigateBack({
          delta: 1
        })
      })
    })
  },

  /**
   * 图片预览
   */
  previewImage: function() {
    var that = this;
    wx.previewImage({
      urls: [that.data.msgInfo.msg_img],
    })
  }

})