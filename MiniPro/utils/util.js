var siteroot = 'https://test.kekexunxun.com/mini/';

//添加finally：因为还有一个参数里面还有一个complete方法
Promise.prototype.finally = function(callback) {
  let P = this.constructor;
  return this.then(
    value => P.resolve(callback()).then(() => value),
    reason => P.resolve(callback()).then(() => {
      throw r
    })
  );
};

//封装异步api
const wxPromisify = fn => {
  return function(obj = {}) {
    return new Promise((resolve, reject) => {
      obj.success = function(res) {
        resolve(res)
      }
      obj.fail = function(res) {
        reject(res)
      }
      fn(obj)
    })
  }
}

const locationPromisified = wxPromisify(wx.getLocation); // 获取经纬度
const modalPromisified = wxPromisify(wx.showModal); // 弹窗
const loginPromisified = wxPromisify(wx.login); // 登陆
const addressPromisified = wxPromisify(wx.chooseAddress); // 获取地址
const settingPromisified = wxPromisify(wx.getSetting); // 获取设置

// 封装文件上传请求
const fileUpload = (url, filePath, formData, timeout = 600) => {
  url = siteroot + url;
  var promise = new Promise((resolve, reject) => {
    //网络请求
    wx.showNavigationBarLoading();
    setTimeout(function() {
      wx.uploadFile({
        url: url,
        filePath: filePath,
        name: 'file',
        formData: formData,
        success: function(res) {
          wx.hideLoading();
          let data = JSON.parse(res.data);
          if (res.statusCode == 200 && data.code == 0) {
            resolve(data.data);
          } else {
            reject(res);
          }
        },
        fail: function(res) {
          console.log(res);
          wx.hideLoading();
          reject(res);
        },
        complete: function() {
          wx.hideNavigationBarLoading();
        }
      })
    }, timeout)
  });
  return promise;
}

// 封装post请求
const post = (url, data, timeout = 600) => {
  url = siteroot + url;
  var promise = new Promise((resolve, reject) => {
    //网络请求
    wx.showNavigationBarLoading();
    setTimeout(function() {
      wx.request({
        url: url,
        data: data,
        method: 'POST',
        success: function(res) { //服务器返回数据
          console.log(res);
          wx.hideLoading();
          if (res.statusCode == 200 && res.data.code == 0) {
            resolve(res.data.data);
          } else if (res.statusCode != 200) {
            modalPromisified({
              title: '系统提示',
              content: '网络错误，请检查网络后重试',
              showCancel: false
            })
          } else { //返回错误提示信息
            reject(res);
          }
        },
        fail: function(error) {
          console.log(error);
          wx.hideLoading();
          modalPromisified({
            title: '系统错误',
            content: error.toString(),
            showCancel: false
          })
        },
        complete: function() {
          wx.hideNavigationBarLoading();
          wx.stopPullDownRefresh();
        }
      })
    }, timeout)
  });
  return promise;
}

module.exports = {
  post,
  modalPromisified,
  loginPromisified,
  locationPromisified,
  addressPromisified,
  settingPromisified,
  fileUpload
}