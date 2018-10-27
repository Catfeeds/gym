function check() {
  if (!$('#account').val()) {
    layer.msg('用户名不能为空!', { icon: 2, time: 2000 });
  } else if (!$('#pwd').val()) {
    layer.msg('密码不能为空!', { icon: 2, time: 2000 });
  } else {
    var username = $('#account').val();
    var password = md5($('#pwd').val());
    $.ajax({
      url: 'checkLogin',
      type: "POST",
      dataType: "json",
      data: {
        username: username,
        password: password
      },
      success: function (res) {
        if (res.code == "0") {
          layer.msg('登陆成功!', { icon: 1, time: 2000 });
          setTimeout("window.location.href='http://gym.kekexunxun.com/index'", 1000);
        } else {
          layer.msg(res.message, { icon: 2, time: 2000 });
        }
      }
    });
  }
}
// 键盘事件
function keyDown() {
  if (event.keyCode == 13) {  //回车键的键值为13
    $('#loginbtn').click(); //调用登录按钮的登录事件
  }
}
$(document).ready(
  function () {
    $("#gotoPage").keydown(function (event) {
      if (event.keyCode == 13) {
        $('#loginbtn').click();
      }
    })
  }
);