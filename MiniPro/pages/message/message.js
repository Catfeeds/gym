var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    pageNum: 0, // 消息页码
    messageList: [], // 消息列表
    tabs: [{
      name: '系统消息',
      list: [],
      pageNum: 0,
      msgType: 0
    }, {
      name: '用户消息',
      list: [],
      pageNum: 0,
      msgType: 1
    }], // 顶部tab切换列表
    activeIndex: 0, // 当前激活的
    sliderOffset: 0,
    sliderLeft: 0,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;
    var sliderWidth = 96;
    var tabs = that.data.tabs;
    // 计算顶部TAB
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          sliderLeft: (res.windowWidth / tabs.length - sliderWidth) / 2,
          sliderOffset: res.windowWidth / tabs.length * that.data.activeIndex,
          itemWidth: sliderWidth
        });
      }
    })
    // 默认获取系统消息
    that.getMsgList()
  },


  // TAB点击事件
  tabClick: function(evt) {
    var that = this;
    that.setData({
      sliderOffset: evt.currentTarget.offsetLeft,
      activeIndex: evt.currentTarget.id
    });
    // console.log(that.data.tabs[evt.currentTarget.id].list.length)
    if (that.data.tabs[evt.currentTarget.id].list.length == 0 && that.data.tabs[evt.currentTarget.id].pageNum == 0) {
      that.getMsgList()
    }
  },

  /**
   * 获取消息列表
   * msgType 0 系统消息 1 用户消息
   */
  getMsgList: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    let curMsg = that.data.tabs[that.data.activeIndex];
    util.post('message/getMsgList', {
      uid: app.globalData.uid,
      pageNum: curMsg.pageNum,
      msgType: curMsg.msgType
    }).then(res => {
      if (!res) return;
      curMsg.list = curMsg.list.concat(res);
      curMsg.pageNum = curMsg.pageNum + 1;
      let tabs = that.data.tabs;
      tabs[that.data.activeIndex] = curMsg;
      that.setData({
        tabs: tabs
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请尝试检查网络后重试',
        showCancel: false
      })
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    // 先初始化
    var that = this;
    var tabs = that.data.tabs;
    tabs[that.data.activeIndex].list = [];
    tabs[that.data.activeIndex].pageNum = 0;
    tabs[that.data.activeIndex].isHaveMore = true;
    that.setData({
      tabs: tabs
    })
    that.getMsgList();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    this.getMsgList();
  },

  /**
   * 跳转到消息详情
   */
  navToMsgDetail: function(evt) {
    wx.navigateTo({
      url: '/pages/messagedetail/messagedetail?msgid=' + evt.currentTarget.dataset.msgid,
    })
  }

})