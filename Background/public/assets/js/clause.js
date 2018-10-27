   $(function(){
    $('.skin-minimal input').iCheck({
        checkboxClass: 'icheckbox-blue',
        radioClass: 'iradio-blue',
        increaseArea: '20%'
    });
    UE.getEditor('container');
}); 
// 表单提交
function agreement_save_submit() {
    if(UE.getEditor('container').getContent()==''){
        layer.msg('协议不能为空！',{icon:2,time:1000});
        return false;
    }else{
        $.ajax({
            type: 'POST',
            url: 'updateClause',
            dataType: 'json',
            data:$("#form-agreement-add").serialize(),
            success: function(data){
                if(data.code=='0'){
                    layer.msg(data.msg,{icon:1,time:1000});
                    setTimeout("location.replace(location.href)", 1000);
                }
                if(data.code=='400'){
                    layer.msg(data.msg,{icon:2,time:1000});
                }
            },
            error:function(data) {
                layer.msg('请稍后再试！',{icon:2,time:1000});
            },
        });
    }
}
// 清空输入框值
function layer_close(){
    UE.getEditor('container').setContent('');
}