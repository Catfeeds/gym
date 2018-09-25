var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    courseInfo: [], // 课程详情
  },

  onLoad: function(options) {
    // 点击统计

    // this.getCourseDetail();

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
      
    }).catch(res => {})

  },

  /**
   * 购买课程
   */
  payCourse: function(e) {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '确定要购买该课程吗？',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '下单中...',
            mask: true
          })
          wx.request({
            url: app.globalData.siteroot + 'order/createOrder',
            method: 'POST',
            dataType: 'json',
            data: {
              uid: app.globalData.uid,
              courseId: that.data.courseInfo.course_id,
              // fee: that.data.courseInfo.course_price
              fee: 0.01
            },
            success: function(res) {
              if (res.statusCode == 200 && res.data.code == 0) {
                wx.showToast({
                  title: '下单成功'
                })
                wx.hideLoading();
                wx.showLoading({
                  title: '支付请求中...',
                  mask: true
                })
                setTimeout(function() {
                  that.makePay(res.data.data.orderid);
                }, 700)
              } else if (res.data.code == 401) {
                wx.hideLoading();
                wx.showModal({
                  title: '系统提示',
                  content: '参数错误，请下拉刷新后重试',
                  showCancel: false
                })
              } else if (res.data.code == 402) {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
              } else {
                wx.showToast({
                  title: '下单失败',
                  icon: 'loading'
                })
              }
            }
          })
        }
      }
    })
  },

  makePay: function(orderid) {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'wxpay/createWxPay',
      method: 'POST',
      dataType: 'json',
      data: {
        fee: that.data.courseInfo.course_price,
        openid: wx.getStorageSync('openid'),
        orderid: orderid,
        course: that.data.courseInfo.course_name,
        uid: wx.getStorageSync('uid'),
        courseid: that.data.courseId
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          var payInfo = res.data.data;
          wx.requestPayment({
            timeStamp: payInfo.timeStamp,
            nonceStr: payInfo.nonce_str,
            package: 'prepay_id=' + payInfo.prepay_id,
            signType: 'MD5',
            paySign: payInfo.paySign,
            success: function() {
              wx.hideLoading();
              wx.showLoading({
                title: '支付结果确认中',
                mask: true
              })
              setTimeout(function() {
                that.checkWxpay(payInfo.orderid)
              }, 900)
            },
            fail: function() {
              wx.showToast({
                title: '支付失败',
                icon: 'loading',
                mask: true
              })
              setTimeout(function() {
                wx.navigateTo({
                  url: '../myorder/myorder',
                })
              }, 1000)
            }
          })
        } else {
          wx.hideLoading();
          wx.showModal({
            title: '系统提示',
            content: '参数错误，请下拉刷新界面后重试',
            showCancel: false
          })
        }
      },
      complete: function() {
        wx.hideLoading();
      }
    })
  },

  checkWxpay: function(ordersn) {
    wx.request({
      url: app.globalData.siteroot + 'wxpay/checkWxpay',
      method: 'POST',
      dataType: 'json',
      data: {
        orderid: ordersn
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          wx.showToast({
            title: '支付成功'
          })
          setTimeout(function() {
            wx.navigateTo({
              url: '../myorder/myorder',
            })
          }, 1000)
        } else {
          wx.hideLoading();
          wx.showModal({
            title: '系统提示',
            content: '支付校验失败，请联系管理员核对相关信息',
            showCancel: false,
            success: function(res) {
              if (res.confirm) {
                wx.navigateTo({
                  url: '../myorder/myorder',
                })
              }
            }
          })
        }
      },
      complete: function() {
        wx.hideLoading();
      }
    })
  },

  /**
   * 用户
   */
  onPullDownRefresh: function() {
    var that = this;
    wx.showNavigationBarLoading();
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    if (!app.globalData.uid) {
      app.getUserAccountInfo()
    }
    setTimeout(function() {
      that.getCourseDetail()
    }, 600)
  }
})