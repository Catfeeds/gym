const app = getApp();
var util = require('../../utils/util.js');
var WxParse = require('../../utils/wxParse/wxParse.js');

Page({

  data: {
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
      // 初始化
      /**
       * WxParse.wxParse(bindName , type, data, target,imagePadding)
       * 1.bindName绑定的数据名(必填)
       * 2.type可以为html或者md(必填)
       * 3.data为传入的具体数据(必填)
       * 4.target为Page对象,一般为this(必填)
       * 5.imagePadding为当图片自适应是左右的单一padding(默认为0,可选)
       */
      WxParse.wxParse('clause', 'html', res, that, 5);
    }).catch(res => {}).finally(res => {})
  }

})