/*选择生日日期*/
$('#teacher_birth').on('click',function(){
    let date = new Date();
	WdatePicker({
		dateFmt:'yyyy-MM-dd',
		maxDate: date.getFullYear() + '-' + date.getMonth() + '-' + date.getDay(),
	});
});
/*字数限制*/
function textarealength(obj,number){
    var value = $(obj).val();
    if( value.length > number ){
        value = value.substring(0,number);
        $(obj).val(value);
    }
    $(obj).next().find('em').html(value.length);
}
$(function(){
    jQuery.validator.addMethod('isMobile',function(value,element){
        var length = value.length;
        var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
        return (length == 11 && mobile.test(value));
    },"请输入正确的手机号码");
    /*表单提交*/
    $("#form-teacher-add").validate({
        rules: {
            teacher_name : {
            	required : true,
            	maxlength: 20, 
            },
            teacher_phone: {
            	required : true,
            	digits   : true,
                isMobile : true,
            },
            teacher_birth: {
            	required : true,
            	maxlength: 10,
            	minlength: 10,
            },
            teacher_gender:{
            	required : true,
            	digits   : true,
            	range    : [0,1],
            }
        },
        onkeyup:false,
        success:"valid",
        submitHandler:function(form){
            layer.confirm('确定提交吗?',function(){
                $(form).ajaxSubmit({
                    type : 'POST',
                    url  : '/index/course/addteacher',
                    typeData : 'JSON',
                    success : function (result){
                        if( result.code < 400 ){
                            layer.alert(result.msg,{icon:1,closeBtn:0},function(){
                                window.parent.location.href = window.parent.location.href;
                            });
                        }else{
                            layer.alert(result.msg,{icon:2});
                        }
                    },
                    error : function(){
                        layer.alert('请求失败!',{icon:2});
                    }
                });
            });
        }
    });
});