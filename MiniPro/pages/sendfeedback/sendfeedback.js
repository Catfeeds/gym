var app = getApp();
var check = require('../../utils/check.js');
var util = require('../../utils/util.js');

Page({

  data: {
    message: '',
  },

  onLoad: function() {},

  /**
   * 输入反馈内容
   */
  inputMessage: function(evt) {
    this.setData({
      message: check.stringTrim(evt.detail.value)
    })
  },

  /**
   * 选择图片并展示
   */
  chooseImage: function() {
    var that = this;
    wx.chooseImage({
      count: 1, // 默认9
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album'], // 可以指定来源是相册还是相机，默认二者都有
      success: function(res) {
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
        that.setData({
          image: res.tempFilePaths
        })
      }
    })
  },

  /**
   * 预览图片
   */
  previewImage: function() {
    wx.previewImage({
      urls: this.data.image,
    })
  },

  /**
   * 提交反馈
   */
  submitFeedback: function() {
    var that = this;
    // 判断是否可以提交反馈
    if (!that.data.message) {
      util.modalPromisified({
        title: '系统提示',
        content: '反馈内容不能为空',
        showCancel: false
      })
      return;
    }
    util.modalPromisified({
      title: '系统提示',
      content: '您确定要提交当前反馈吗？',
    }).then(res => {
      // 用户点击取消
      if (res.cancel) return;
      // 用户点击确认
      wx.showLoading({
        title: '提交中',
        mask: true
      })
      // 如果有图片 则使用图片上传的方法
      if (that.data.image) {
        util.fileUpload('user/submitFeedback', that.data.image[0], {
          uid: app.globalData.uid,
          userType: app.globalData.userType,
          message: that.data.message
        }, 300).then(res => {
          wx.showToast({
            title: '提交反馈成功',
            duration: 800,
            mask: true
          })
          setTimeout(res => {
            wx.navigateBack({
              delta: 1
            })
          }, 800)
        }).catch(res => {
          util.modalPromisified({
            title: '系统提示',
            content: '网络错误，请检查网络后重试',
            showCancel: false
          })
        })
      } else {
        // 如果没有图片就直接上传
        util.post('user/submitFeedback', {
          uid: app.globalData.uid,
          userType: app.globalData.userType,
          message: that.data.message
        }, 400).then(res => {
          wx.showToast({
            title: '提交反馈成功',
            duration: 800,
            mask: true
          })
          setTimeout(res => {
            wx.navigateBack({
              delta: 1
            })
          }, 800)
        }).catch(res => {
          util.modalPromisified({
            title: '系统提示',
            content: '网络错误，请检查网络后重试',
            showCancel: false
          })
        })
      }
    })
  },


})