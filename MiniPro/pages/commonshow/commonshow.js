const app = getApp();
var util = require('../../utils/util.js');

// 通用的展示界面
// 关于我们 项目展示 Banner跳转
// scene = 1 关于我们 scene = 2 普通的项目展示

Page({

  data: {
    descInfo: [] // 详情
  },

  onLoad: function(options) {
    if (options.scene == 1) {
      wx.setNavigationBarTitle({
        title: '关于我们',
      })
    } else if (options.scene == 2 && !options.id) {
      util.modalPromisified({
        title: '系统提示',
        content: '参数错误，请及时联系管理员',
        showCancel: false
      }).then(res => {
        wx.navigateBack({
          delta: 1
        })
      })
      return;
    }
    this.setData({
      scene: options.scene,
      sceneId: options.id || ""
    })
    this.getCommonShow();
  },

  /**
   * 获取通用展示界面
   */
  getCommonShow: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    let url = that.data.scene == 1 ? 'minibase/getAboutUs' : 'project/getProjectDesc';
    util.post(url, {
      uid: app.globalData.uid,
      pid: that.data.sceneId
    }, 100).then(res => {
      that.setData({
        descInfo: res || []
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '系统错误，请及时联系管理员',
        showCancel: false
      }).then(res => {
        wx.navigateBack({
          delta: 1
        })
      })
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})