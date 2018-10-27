/*字数限制*/
function textarealength(obj,number){
    var value = $(obj).val();
    if( value.length > number ){
        value = value.substring(0,number);
        $(obj).val(value);
    }
    $(obj).next().find('em').html(value.length);
}
// 日期选择
function selecttime(){   
    var startTime = $("#countTimestart").val();
    if(startTime != "" || !startTime){
        WdatePicker({dateFmt:'yyyy-MM-dd', minDate:'',maxDate:''});
    }else{
        WdatePicker({dateFmt:'yyyy-MM-dd', minDate:'',maxDate:''});
    }
}
// 表单正则
$(function(){
    jQuery.validator.addMethod('isMobile',function(value,element){
        var length = value.length;
        var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
        return (length == 11 && mobile.test(value));
    },"请输入正确的手机号码");
    /*表单提交*/
    $("#form-class-add").validate({
        rules: {
            user_name : {
            	required : true,
            	maxlength: 28, 
            },
            user_no : {
                required : true,
                maxlength: 18,
                digits:true
            },
            user_phone : {
                required : true,
                maxlength: 11,
                digits:true,
                isMobile : true
            },
        },
        onkeyup:false,
        success:"valid",
        submitHandler:function(form){
            layer.confirm('确定提交吗?',function(){
                $(form).ajaxSubmit({
                    type : 'POST',
                    url  : '../../editUser',
                    typeData : 'JSON',
                    success : function (result){
                        if(result.code == 0){
                            layer.alert(result.msg,{icon:1,closeBtn:0},function(){
                                window.location.href = window.location.href;
                            });
                        }
                        if(result.code == 400){
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