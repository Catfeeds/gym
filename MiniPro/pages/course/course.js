var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    courseList: [], // 课程详情
  },

  onLoad: function(options) {
    this.getCourseDetail();
  },

  previewImage: function() {
    var that = this;
    wx.previewImage({
      urls: [that.data.courseInfo.course_img],
    })
  },

  /**
   * 获取课程详细细节
   */
  getCourseDetail: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: 'true'
    })
    util.post('user/getCourseInfo', {
      uid: app.globalData.uid
    }).then(res => {
      that.setData({
        courseList: res || []
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络连接后重试',
        confirmText: '重试',
        cancelText: '退出'
      }).then(res => {
        if (res.confirm) that.getClassStuList();
        if (res.cancel) wx.navigateBack({
          delta: 1
        })
      })
    })

  },

  /**
   * 课程续费
   */
  classRenew: function(evt) {
    var that = this;
    let idx = evt.currentTarget.dataset.idx;
    util.modalPromisified({
      title: '系统提示',
      content: '您确认要立即续费当前课程吗？',
      confirmText: '立即续费',
      cancelText: '我再想想'
    }).then(res => {
      if (res.cancel) return;
      that.makeOrder(evt.currentTarget.dataset.classid);
    })
  },

  /**
   * 给老师打电话
   */
  callTeacher: function(evt) {
    util.modalPromisified({
      title: '系统提示',
      content: '您确定要向老师拨打电话吗？',
      confirmText: '确定',
      cancelText: '稍等'
    }).then(res => {
      if (res.confirm) wx.makePhoneCall({
        phoneNumber: evt.currentTarget.dataset.phone,
      })
    })
  },

  /**
   * 发起续费下单
   */
  makeOrder: function(classid) {
    var that = this;
    wx.showLoading({
      title: '下单中',
      mask: true
    })
    util.post('order/createOrder', {
      uid: app.globalData.uid,
      classid: classid
    }).then(res => {
      wx.showToast({
        title: '下单成功',
        duration: 500
      })
      setTimeout(function() {
        that.createWxPay(res);
      }, 500)
    }).catch(res => {
      if (res.data.code == 402) {
        util.modalPromisified({
          title: '系统提示',
          content: '当前课程已失效，请及时联系管理员',
          showCancel: false
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误，请检查网络后重试',
          showCancel: false
        })
      }
    })
  },

  /**
   * 微信支付发起
   */
  createWxPay: function(ordersn) {
    var that = this;
    wx.showLoading({
      title: '支付发起中',
      mask: true
    })
    util.post('wxpay/createWxPay', {
      ordersn: ordersn,
      uid: app.globalData.uid,
      timestamp: parseInt(Date.now() / 1000), // 时间戳校验
      openid: wx.getStorageSync('openid')
    }).then(res => {
      console.log(res)
      // 微信支付请求成功
      wx.showToast({
        title: '支付发起成功',
        mask: true,
        duration: 1000
      })
      return util.paymentPromisified({
        timeStamp: res.timeStamp,
        nonceStr: res.nonce_str,
        package: 'prepay_id=' + res.prepay_id,
        signType: 'MD5',
        paySign: res.paySign
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络后重试',
        showCancel: false
      })
    }).then(res => {
      // 微信支付成功
      wx.showLoading({
        title: '支付校验中',
        mask: true
      })
      return util.post('wxpay/checkWxpay', {
        openid: wx.getStorageSync('openid'),
        ordersn: ordersn
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络后重试',
        showCancel: false
      })
    }).then(res => {
      // 微信支付校验成功
      wx.showToast({
        title: '支付校验成功',
        mask: true,
        duration: 1000
      })
      // 重新刷新当前课程
      setTimeout(function() {
        that.getCourseDetail();
      }, 1000)
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '支付校验失败，请及时联系管理员',
        showCancel: false
      })
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    this.getCourseDetail();
  }

})