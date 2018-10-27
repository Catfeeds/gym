/*选择生日日期*/
$('#teacher_birth').on('click',function(){
    let date = getNowFormatDate();
	WdatePicker({
		dateFmt:'yyyy-MM-dd',
		maxDate:date,
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
    $("#form-teacher-edit").validate({
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
            layer.confirm('确定保存吗?',function(){
                $(form).ajaxSubmit({
                    type : 'POST',
                    url  : '/index/course/editteacher',
                    typeData : 'JSON',
                    success : function (result){
                        if( result.code < 400 ){
                            layer.alert(result.msg,{icon:1,closeBtn:0},function(){
                                window.location.href = window.location.href;
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

function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "-";
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = year + seperator1 + month + seperator1 + strDate;
    return currentdate;
}