var app = getApp();
var check = require('../../utils/check.js');
var util = require('../../utils/util.js');

Page({

  data: {
    message: '',
    classIdx: 0, // 选择的班级列表
    classList: [], // 该老师所带的班级列表
    stuList: [], // 班级对应的学生列表
    isAllSelect: false, // 是否全选
  },

  onLoad: function() {
    this.getTearcherClass();
  },

  /**
   * 获取该教师所带相关班级的数据
   */
  getTearcherClass: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    util.post('teacher/getTearcherClass', {
      tid: app.globalData.uid  // 这里的UID实际上是教师的TID
    }).then(res => {
      if (res) {
        // 构造班级的picker
        let classPicker = [];
        classPicker.push('请选择班级');
        for (let i = 0; i < res.length; i++) {
          classPicker.push(res[i].class_name + ' - ' + res[i].class_time);
        }
        that.setData({
          classList: res,
          classPicker: classPicker
        })
      } else {
        that.setData({
          isHaveClass: false
        })
      }
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络连接后重试',
        confirmText: '重试'
      }).then(res => {
        if (res.confirm) that.getTearcherClass();
      })
    })
  },

  /**
   * 教师选择班级
   */
  chooseClass: function(evt) {
    this.setData({
      classIdx: evt.detail.value
    })
    if (evt.detail.value != 0) {
      this.getClassStudent();
    } else {
      this.setData({
        stuList: []
      })
    }
  },

  /**
   * 获取班级对应的学生
   */
  getClassStudent: function() {
    var that = this;
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    util.post('teacher/getClassStudent', {
      tid: app.globalData.uid,
      classid: that.data.classList[that.data.classIdx - 1].class_id
    }, 300).then(res => {
      let stuList = [];
      stuList.push({
        uid: 0,
        username: '全选',
        select: false
      })
      that.setData({
        stuList: stuList.concat(res)
      })
    }).catch(res => {
      if (res.data.code == 402) {
        util.modalPromisified({
          title: '系统提示',
          content: '当前班级暂无学生，请重新选择',
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
   * 选择需要发送消息的学生
   */
  selectStudent: function(evt) {
    var that = this;
    let stuList = that.data.stuList,
      idx = evt.currentTarget.dataset.idx,
      isAllSelect = that.data.isAllSelect;
    // 教师选择全选
    if (idx == 0) {
      for (let i = 0; i < stuList.length; i++) {
        stuList[i].select = !isAllSelect;
      }
      isAllSelect = !isAllSelect;
    } else {
      isAllSelect = !isAllSelect;
      stuList[idx].select = !stuList[idx].select;
      // 全选判定
      for (let i = 0; i < stuList.length; i++) {
        if (i == 0) continue;
        if (stuList[i].select != isAllSelect) {
          isAllSelect = !isAllSelect
          break;
        }
      }
    }
    that.setData({
      isAllSelect: isAllSelect,
      stuList: stuList
    })
  },

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
  sendMessage: function() {
    var that = this;
    // 判断是否可以提交反馈
    if (!that.isCanSendMsg()) return;

    util.modalPromisified({
      title: '系统提示',
      content: '您确定要发送该消息吗？',
    }).then(res => {
      // 用户点击取消
      if (res.cancel) return;
      // 用户点击确认
      wx.showLoading({
        title: '发送中',
        mask: true
      })
      // 构造 附带发送的消息
      let data = {
        uid: app.globalData.uid,
        message: that.data.message,
        stulist: that.data.stuList,
        classid: that.data.classList[that.data.classIdx - 1].class_id
      };
      // 如果有图片 则使用图片上传的方法
      if (that.data.image) {
        util.fileUpload('teacher/sendMessage', that.data.image[0], data, 300).then(res => {
          wx.showToast({
            title: '消息发送成功',
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
        util.post('teacher/sendMessage', data, 400).then(res => {
          wx.showToast({
            title: '发送消息成功',
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

  /**
   * 判断教师是否可以发送这个消息
   */
  isCanSendMsg: function() {
    var that = this;
    // 班级选择
    if (that.data.classIdx == 0) {
      util.modalPromisified({
        title: '系统提示',
        content: '请选择需要发送消息的班级',
        showCancel: false
      })
      return false;
    }
    // 学生选择
    let stuList = that.data.stuList,
      isSelectStu = false;
    for (let i = 0; i < stuList.length; i++) {
      if (i == 0) continue;
      if (stuList[i].select) {
        isSelectStu = true;
        break;
      }
    }
    if (!isSelectStu) {
      util.modalPromisified({
        title: '系统提示',
        content: '请至少选择一名学生',
        showCancel: false
      })
      return false;
    }
    // 消息内容输入判断
    if (!that.data.message) {
      util.modalPromisified({
        title: '系统提示',
        content: '消息内容不能为空',
        showCancel: false
      })
      return false;
    }
    // 没有问题
    return true;
  },

})