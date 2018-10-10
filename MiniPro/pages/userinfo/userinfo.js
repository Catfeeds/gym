var app = getApp();
var util = require('../../utils/util.js');
var check = require('../../utils/check.js');

Page({

  data: {
    showSendCode: true, // 是否展示发送验证码按钮
    isTelCanInput: true, // 用户是否可以输入手机号
  },

  onLoad: function() {
    this.getUser();
  },

  /**
   * 获取当前用户的相关信息
   */
  getUser: function() {
    var that = this;
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    util.post('user/getUserInfo', {
      uid: app.globalData.uid,
      userType: app.globalData.userType
    }).then(res => {
      // 如果是会员
      let isMember = app.globalData.userType == 1 ? true : false;
      that.setData({
        isMember: isMember,
        userInfo: res
      })
    }).catch(res => {
      if (res.data.code == 401) {
        util.modalPromisified({
          title: '系统提示',
          content: '参数错误，请及时联系管理员',
          showCancel: false
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误，请尝试检查网络后重试',
          showCancel: false
        })
      }
    })
  },

  /**
   * 验证码失效判定
   */
  onShow: function() {
    var that = this;
    let codeTime = wx.getStorageSync('codeTime');
    if (codeTime && codeTime > parseInt(Date.now() / 1000)) {
      that.setData({
        showSendCode: false
      })
      that.setCodeCountdown();
    } else {
      wx.removeStorageSync('codeTime');
    }
  },

  /**
   * 输入手机号
   */
  inputTel: function(e) {
    this.setData({
      userTel: check.stringTrim(e.detail.value)
    })
  },

  /**
   * 输入验证码
   */
  inputValidateCode: function(evt) {
    var that = this;
    var validateCode = evt.detail.value;
    if (validateCode.length == 6) {
      if (validateCode == that.data.validateCode) {
        wx.showToast({
          title: '验证成功',
        })
        that.setData({
          isValidate: true,
          isTelCanInput: false
        })
      } else {
        wx.showToast({
          title: '验证码错误',
          icon: 'none'
        })
      }
    }
  },

  /**
   * 获取手机验证码
   */
  getValidCode: function() {
    var that = this;
    let userTel = that.data.userTel;
    // 如果已经认证 但是没有更换手机号码也是不能获取验证码的
    if (userTel == that.data.userInfo.phone) {
      util.modalPromisified({
        title: '系统提示',
        content: '手机号码未变更，请检查后重试',
        showCancel: false
      })
      return;
    }
    if (!userTel || userTel.length != 11) {
      util.modalPromisified({
        title: '系统提示',
        content: '手机号码格式错误，请检查后重试',
        showCancel: false
      })
      return;
    }
    util.post('sms/sendSingleSms', {
      openid: wx.getStorageSync('openid'),
      telnum: userTel,
      usertype: app.globalData.usertype,
      check: 0 // 是否需要去检查数据库
    }, 200).then(res => {
      wx.showToast({
        title: '验证码发送成功',
        duration: 1000
      })
      // console.log(res);
      // 将验证码Code放在本地缓存
      wx.setStorageSync('code', res);
      // 设置验证码倒计时
      wx.setStorageSync('codeTime', parseInt(Date.now() / 1000 + 60));
      that.setCodeCountdown();
      that.setData({
        showSendCode: false,
        validateCode: res,
        isTelCanInput: false, // 让用户不能再输入手机号
        showValidateInput: true, // 显示验证码输入框
        codeCountdown: 60,
        isValidate: false
      })
    }).catch(res => {
      if (res.data.code == 402) {
        util.modalPromisified({
          title: '系统提示',
          content: '手机号码错误，请核对后重试',
          showCancel: false
        })
      } else if (res.data.code == 401) {
        util.modalPromisified({
          title: '系统提示',
          content: '当前手机号不为该系统用户，请联系管理员或检查手机号后重试',
          confirmText: '联系管理'
        }).then(res => {
          if (res.confirm) {
            wx.makePhoneCall({
              phoneNumber: app.globalData.setting.service_phone,
            })
          }
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
   * 用户验证码倒计时
   */
  setCodeCountdown: function() {
    var that = this;
    let codeTime = wx.getStorageSync('codeTime');
    let intval = setInterval(res => {
      let codeCountdown = codeTime - parseInt(Date.now() / 1000);
      if (codeCountdown == -1) {
        clearInterval(intval);
        that.setData({
          showSendCode: true,
          isTelCanInput: true
        })
        wx.removeStorageSync('codeTime');
      } else {
        that.setData({
          codeCountdown: codeCountdown
        })
      }
    }, 1000)
  },

  /**
   * 用户更换验证手机
   */
  userChangePhone: function() {
    var that = this;
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    util.post('user/changePhone', {
      usertype: app.globalData.userType,
      uid: app.globalData.uid,
      mobile: that.data.userTel
    }).then(res => {
      wx.showToast({
        title: '修改成功',
        duration: 1000
      })
      that.setData({
        showValidateInput: false,
        isValidate: false,
        validateCode: "",
        isTelCanInput: true,
        showSendCode: true
      })
      wx.removeStorageSync('codeTime');
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络后重试',
        showCancel: false
      })
    })

  },

  /**
   * 生命周期函数--监听页面卸载
   * 当用户获取完验证码并返回时，将可获取验证码有效期保存
   * 当用户验证成功后则不必保存
   */
  onUnload: function() {
    if (!this.data.isAuth) {
      app.globalData.telCodeTimeCount = this.data.refreshCodeTime
    }
  },


})