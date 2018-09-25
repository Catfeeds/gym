var app = getApp();
var util = require('../../utils/util.js');
var check = require('../../utils/check.js');

Page({

  data: {
    isCanClick: true, // 是否可以点击授权按钮
    isTelAuth: false, // 用户手机号是否绑定
    userName: '', // 用户姓名
    userTel: '', // 用户手机号
    isCanGetCode: true, // 是否可以获取手机验证码
    identifyCode: '', // 用户验证码
    // isCanEditMobile: true, // 是否可以修改手机号
    userType: 0, // 用户身份 0 学生 1教师
  },

  onLoad: function(options) {},

  /**
   * 判断获取验证码状态
   */
  onShow: function() {
    var that = this;
    let codeTime = wx.getStorageSync('codeTime');
    if (codeTime && codeTime > parseInt(Date.now() / 1000)) {
      that.setData({
        isCanGetCode: false
      })
      that.setCodeCountdown();
    } else {
      wx.removeStorageSync('codeTime');
    }
  },

  /**
   * 勾选相关条款按钮
   */
  bindAgreeChange: function() {
    var that = this;
    var isAgree = that.data.isAgree ? !that.data.isAgree : true;
    that.setData({
      isAgree: isAgree
    })
  },

  /**
   * 获取手机验证码
   */
  getIdentifyCode: function(evt) {
    var that = this;
    if (!that.data.userTel || that.data.userTel.length != 11) {
      util.modalPromisified({
        title: '系统提示',
        content: '手机号码格式错误',
        showCancel: false
      })
      return;
    }
    // 获取请求手机号请求
    wx.showLoading({
      title: '验证码发送中',
      mask: true
    })
    util.post('sms/sendSingleSms', {
      openid: wx.getStorageSync('openid'),
      telnum: that.data.userTel,
      usertype: that.data.userType
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
        isCanGetCode: false,
        code: res
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
          wx.navigateTo({
            url: '/pages/storeinfo/storeinfo',
          })
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
          isCanGetCode: true
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
   * 用户输入手机号
   */
  inputTel: function(evt) {
    this.setData({
      userTel: check.stringTrim(evt.detail.value)
    })
  },

  /**
   * 用户输入姓名
   */
  inputName: function(evt) {
    this.setData({
      userName: check.stringTrim(evt.detail.value)
    })
  },

  /**
   * 用户输入验证码
   */
  inputCode: function(evt) {
    this.setData({
      identifyCode: check.stringTrim(evt.detail.value)
    })
  },

  /**
   * 用户选择认证身份
   */
  chooseUserType: function(evt){
    this.setData({
      userType: evt.currentTarget.dataset.id
    })
  },

  /**
   * 获取用户信息
   */
  getUserInfo: function(evt) {
    var that = this;
    if (!evt.detail.userInfo) {
      util.modalPromisified({
        title: '系统提示',
        content: '微信授权失败，请重试',
        showCancel: false
      })
      return;
    }

    // 验证码检测
    if (!that.data.identifyCode || that.data.identifyCode.length != 6 || that.data.identifyCode != that.data.code || !wx.getStorageSync('code') || that.data.identifyCode != wx.getStorageSync('code')) {
      util.modalPromisified({
        title: '系统提示',
        content: '验证码错误，请核验后重试',
        showCancel: false
      })
      return;
    }
    wx.showLoading({
      title: '登录中',
      mask: true
    })
    // 登陆
    util.post('user/setUserInfo', {
      openid: wx.getStorageSync('openid'),
      userInfo: evt.detail.userInfo,
      uid: app.globalData.uid,
      mobile: that.data.userTel,
      authname: that.data.userName,
      usertype: that.data.userType
    }).then(res => {
      wx.showToast({
        title: '登陆成功',
        duration: 800
      })
      wx.removeStorageSync('code');
      wx.removeStorageSync('codeTime');
      app.globalData.userInfo = evt.detail.userInfo;
      app.globalData.isAuth = true;
      setTimeout(res => {
        wx.redirectTo({
          url: '/pages/index/index'
        })
      }, 800)
    }).catch(res => {
      if (res.data.code == 402) {
        util.modalPromisified({
          title: '系统提示',
          content: '登陆失败，请及时联系管理员',
          showCancel: false
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误，登陆失败，请重新尝试',
          showCancel: false
        })
      }
    })
  },


})