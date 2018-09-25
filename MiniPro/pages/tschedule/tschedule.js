const app = getApp();
var util = require('../../utils/util.js');

Page({
  data: {
    selectedDate: '', //选中的几月几号
    selectedWeek: '', //选中的星期几
    curYear: 2017, //当前年份
    curMonth: 0, //当前月份
    daysCountArr: [ // 保存各个月份的长度，平年
      31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31
    ],
    weekArr: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
    dateList: []
  },

  onLoad: function(options) {
    var today = new Date(); //当前时间  
    var y = today.getFullYear(); //年  
    var mon = today.getMonth() + 1; //月  
    var d = today.getDate(); //日  
    var i = today.getDay(); //星期  
    this.setData({
      curYear: y,
      curMonth: mon,
      selectedDate: y + '-' + mon + '-' + d,
      selectedWeek: this.data.weekArr[i]
    });

    this.getDateList(y, mon - 1);
  },

  onShow: function() {
    this.getTeacherSchedule();
  },

  getDateList: function(y, mon) {
    var that = this;
    //如果是否闰年，则2月是29日
    var daysCountArr = this.data.daysCountArr;
    if (y % 4 == 0 && y % 100 != 0) {
      this.data.daysCountArr[1] = 29;
      this.setData({
        daysCountArr: daysCountArr
      });
    }
    //第几个月；下标从0开始实际月份还要再+1  
    var dateList = [];
    // console.log('本月', that.data.daysCountArr[mon], '天');
    dateList[0] = [];
    var weekIndex = 0; //第几个星期
    for (var i = 0; i < that.data.daysCountArr[mon]; i++) {
      var week = new Date(y, mon, (i + 1)).getDay();
      // 如果是新的一周，则新增一周
      if (week == 0) {
        weekIndex++;
        dateList[weekIndex] = [];
      }
      // 如果是第一行，则将该行日期倒序，以便配合样式居右显示
      if (weekIndex == 0) {
        dateList[weekIndex].unshift({
          value: y + '-' + (mon + 1) + '-' + (i + 1),
          date: i + 1,
          week: week
        });
      } else {
        dateList[weekIndex].push({
          value: y + '-' + (mon + 1) + '-' + (i + 1),
          date: i + 1,
          week: week
        });
      }
    }
    // console.log('本月日期', dateList);
    that.setData({
      dateList: dateList
    });
  },

  selectDate: function(e) {
    var that = this;
    // console.log('选中', e.currentTarget.dataset.date.value);
    let selectedDate = e.currentTarget.dataset.date.value,
      selectedWeek = that.data.weekArr[e.currentTarget.dataset.date.week];
    that.setData({
      selectedDate: selectedDate,
      selectedWeek: selectedWeek
    });
    // 选中日期时  展示该教师当前日期课程
    that.getTeacherSchedule();
  },

  preMonth: function() {
    // 上个月
    var that = this;
    var curYear = that.data.curYear;
    var curMonth = that.data.curMonth;
    curYear = curMonth - 1 ? curYear : curYear - 1;
    curMonth = curMonth - 1 ? curMonth - 1 : 12;
    // console.log('上个月', curYear, curMonth);
    that.setData({
      curYear: curYear,
      curMonth: curMonth
    });

    that.getDateList(curYear, curMonth - 1);
  },

  nextMonth: function() {
    // 下个月
    var that = this;
    var curYear = that.data.curYear;
    var curMonth = that.data.curMonth;
    curYear = curMonth + 1 == 13 ? curYear + 1 : curYear;
    curMonth = curMonth + 1 == 13 ? 1 : curMonth + 1;
    // console.log('下个月', curYear, curMonth);
    that.setData({
      curYear: curYear,
      curMonth: curMonth
    });

    that.getDateList(curYear, curMonth - 1);
  },

  /**
   * 获取教师当前日期的课程列表 
   */
  getTeacherSchedule: function() {
    var that = this;
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    util.post('teacher/getSchedule', {
      tid: app.globalData.uid,
      date: that.data.selectedDate
    }, 300).then(res => {
      that.setData({
        schedule: res || []
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络连接后重试',
        confirmText: '重试',
        cancelText: '退出'
      }).then(res => {
        if (res.confirm) that.getTeacherSchedule();
        if (res.cancel) wx.navigateBack({
          delta: 1
        })
      })
    })
  },

  /**
   * 跳转到课程详情界面
   */
  navToClassDetail: function(evt){
    wx.navigateTo({
      url: '/pages/classdetail/classdetail?classid=' + evt.currentTarget.dataset.classid,
    })
  }
})