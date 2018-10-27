/**
 * 字数限制
 */
function textarealength(obj,number){
    var value = $(obj).val();
    if( value.length > number ){
        value = value.substring(0,number);
        $(obj).val(value);
    }
    $(obj).next().find('em').html(value.length);
}
// 在键盘按下并释放及提交后验证提交表单
$(".form-horizontal").validate({
    rules: {
      mini_name: {
        required: true,
        maxlength: 10
      },
      service_phone: {
        required: true,
        maxlength:14,
        number: true
      },
      share_text: {
        required: true,
        maxlength: 30
      },
      store_info: {
        required: true,
        maxlength: 500,
      }           
    },
})
// 表单提交
function save_submit(){
var idx = $("#idx").val();
var mini_name = $("#mini_name").val();
var service_phone = $("#service_phone").val();
var share_text = $("#share_text").val();
var store_info = $("#store_info").val();
    // if(mini_name==''){
    //     layer.msg('小程序名称不能为空！',{icon:2,time:1000});
    //     return false;
    // }
    // if(service_phone==''){
    //     layer.msg('客服电话不能为空！',{icon:2,time:1000});
    //     return false;
    // }
    // if(share_text==''){
    //     layer.msg('分享的文字不能为空！',{icon:2,time:1000});
    //     return false;
    // }
    // if(store_info==''){
    //     layer.msg('门店信息不能为空！',{icon:2,time:1000});
    //     return false;
    // }
    var flag = $(".form-horizontal").valid();
    if(!flag){
        //没有通过验证
        layer.msg('请填写完整信息！', { icon: 2, time: 1000 });
        return false;
    }    
    layer.confirm('确定保存吗?',function(){
        $.ajax({
            type : 'POST',
            url  : 'editProgram',
            typeData : 'JSON',
            data:{idx:idx,mini_name:mini_name,service_phone:service_phone,share_text:share_text,store_info:store_info},
            success : function (result){
                if( result.code == 0 ){
                    layer.alert(result.msg,{icon:1,closeBtn:0},function(){
                        window.location.href = window.location.href;
                    });
                }
                if(result.code == 400){
                    layer.alert(result.msg);
                }
            },
            error : function(){
                layer.alert('请求失败!');
            }
        });
    });    
}