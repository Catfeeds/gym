const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    banner: [{
      banner_img: '/images/banner.png'
    }], // 默认banner
    isCoach: false, // 用户身份是否为教练
    projectList: [], // 项目列表
    pageNum: 0, // 需要查询的项目页码
  },

  onLoad: function() {
    this.getIndex();
  },

  /**
   * 获取首页界面详情 主要是获取banner
   */
  getIndex: function() {
    var that = this;
    // 先进行登陆
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    app.loadInfo().then(res => {
      // 先校验身份
      if (!app.globalData.isAuth) {
        util.modalPromisified({
          title: '系统提示',
          content: '您需要先进行认证才可进行后续操作',
          confirmText: '进行认证',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/userauth/userauth'
          })
        })
      } else {
        return util.post('minibase/getIndex', {
          uid: app.globalData.uid
        }, 100)
      }
    }).then(res => {
      let banner = that.data.banner;
      that.setData({
        banner: res ? res : banner,
        isCoach: app.globalData.userType == 1 ? false : true
      })
      // 根据系统设置 修改小程序名称
      if (app.globalData.setting) {
        wx.setNavigationBarTitle({
          title: app.globalData.setting.mini_name,
        })
      }
      // 获取项目列表
      that.getProjectList();
    }).catch(res => {
      if (res.data.code == 403) {
        util.modalPromisified({
          title: '系统提示',
          content: '您已被禁止进入该小程序，请及时联系管理员',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/nouser/nouser',
          })
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误，请尝试检查网络后重试',
          confirmText: '重新连接'
        }).then(res => {
          if (res.confirm) {
            that.getIndex();
          }
        })
      }
    })
  },

  /**
   * 获取项目列表
   */
  getProjectList: function() {
    var that = this;
    util.post('project/getProject', {
      uid: app.globalData.uid
    }, 300).then(res => {
      that.setData({
        projectList: that.data.projectList.concat(res || []),
        pageNum: that.data.pageNum + 1
      })
    }).catch(res => {
      console.log(res.data.code)
      if (res.data.code == 405) {
        wx.showToast({
          title: '没有更多啦',
          icon: 'loading',
          duration: 1200
        })
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '系统错误，请尝试联系管理员',
          confirmText: '重新连接'
        })
      }
    })
  },

  /**
   * 跳转到通用展示界面
   */
  navToCommonshow: function(evt) {
    wx.navigateTo({
      url: '/pages/commonshow/commonshow?scene=2&id=' + evt.currentTarget.dataset.pid,
    })
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || 'Reshape重塑形体~',
      path: '/pages/index/index'
    }
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh() {
    this.setData({
      pageNum: 0,
      projectList: []
    })
    this.getIndex();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    this.getProjectList();
  }

})