const app = getApp();
var util = require('../../utils/util.js');

// 通用的展示界面
// 关于我们 项目展示 Banner跳转
// scene = 1 关于我们 scene = 2 普通的项目展示

Page({

  /**
   * 页面的初始数据
   */
  data: {
    descInfo: [], // 介绍详情
    isHaveVideo: true, // 是否有视频
    src: "http://wxsnsdy.tc.qq.com/105/20210/snsdyvideodownload?filekey=30280201010421301f0201690402534804102ca905ce620b1241b726bc41dcff44e00204012882540400&bizid=1023&hy=SH&fileparam=302c020101042530230204136ffd93020457e3c4ff02024ef202031e8d7f02030f42400204045a320a0201000400", // 初始化的视频界面
    imgsec: ['https://art.up.maikoo.cn/feedback/20180907/d434efbfabd49ab4511f3d7fe3bd5ba1.png', 'https://art.up.maikoo.cn/feedback/20180907/f31f0290a40d9abe758c489f20bca127.png']
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
      uid: app.globalData.uid
    }, 100).then(res => {
      that.setData({
        descInfo: res
      })
    }).catch(res => {
      if (res.statusCode != 200) {
        util.modalPromisified({
          title: '系统提示',
          content: '当前项目不存在',
          showCancel: false
        }).then(res => {
          wx.navigateBack({
            delta: 1
          })
        })
      }
      if (res.statusCode != 200) {
        util.modalPromisified({
          title: '系统提示',
          content: '当前项目不存在',
          showCancel: false
        }).then(res => {
          wx.navigateBack({
            delta: 1
          })
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误',
          showCancel: false
        }).then(res => {
          wx.navigateBack({
            delta: 1
          })
        })
      }
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