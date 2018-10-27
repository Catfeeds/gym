/*个人信息*/
function myselfinfo(){
	layer.open({
		type: 1,
		area: ['300px','200px'],
		fix: false, //不固定
		maxmin: true,
		shade:0.4,
		title: '查看信息',
		content: '<div style="text-align:center;">管理员信息</div>'
	});
}

function editPassword(){
	// 修改管理员密码
    $("#modal-demotwo").modal("show");
}
    // 点击确定
$("#confirm").click(function(){
    var admin_id = $("#admin_id").text();
    var password = md5($('#password').val());
    var password1 = md5($('#password1').val());
    var password2 = md5($('#password2').val());
    if($('#password').val() == '' || $('#password1').val() == '' || $('#password2').val() == ''){
        layer.msg('密码不能为空！',{icon:2,time:1000});
        return false;
    }else if($('#password1').val() != $('#password2').val() ){
        layer.msg('两次输入的密码不一致！',{icon:2,time:1000});
        return false;
    }        
    // 发送ajax
    $.ajax({
        type: 'POST',
        url: 'index/index/passwordUpdate',
        dataType: 'json',
        data:{admin_id:admin_id,password:password,password1:password1},
        success: function(data){
            if(data.code=="0"){
                layer.msg(data.msg,{icon:1,time:1000});
                setTimeout("location.replace(location.href);",1000)
            }
            if(data.code=='400'){
                layer.msg(data.msg,{icon:2,time:1000});
            }
        },
        error:function(data) {
            layer.msg('网络错误，请稍后重试！',{icon:2,time:1000});
        },
    });	
})