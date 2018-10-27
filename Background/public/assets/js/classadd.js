// 页面加载后执行
window.onload=function (){
    if(!$("#subject_id").val()){
        layer.alert('无科目信息，请添加科目！',{icon:2,closeBtn:0},function(){
            window.parent.location.href = window.parent.location.href;
        });        
    }
    if(!$("#teacher_id").val()){
        layer.alert('无教师信息，请添加教师！',{icon:2,closeBtn:0},function(){
            window.parent.location.href = window.parent.location.href;
        });        
    }
}
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
function selecttime(flag){   
    if(flag==1){
        var endTime = $("#countTimeend").val();
        if(endTime != ""){
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }else{
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }
    }else{
        var startTime = $("#countTimestart").val();
        if(startTime != "" || !startTime){
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }else{
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }
    }
}
// 选择框事件
$('#subject_id').change(function(){
    getSubjectCourse();
});
// 调用方法
getSubjectCourse();
// 请求科目对应课程
function getSubjectCourse(){
    $.ajax({
        type: 'POST',
        url: '../System/getSubjectCourse',
        dataType: 'json',
        data: {subject_id:$('#subject_id').val()},
        success: function (res) {
            if (res.code == 0) {
                $('#course_id option').remove();
                for (var i = 0; i < res.data.length; i++) {
                    var option = $('<option value="'+res.data[i].course_id+'">'+res.data[i].course_name+'</option>');
                    $('#course_id').append(option);
                }
            }
            if (res.code == 400) {
                $('#course_id option').remove();
                layer.alert(res.msg, {icon: 2});
            }
        },
        error: function () {
            layer.msg('请求错误，请稍后重试！', { icon: 2, time: 1300 });
        },
    });        
}
$(function(){
    /*表单提交*/
    $("#form-class-add").validate({
        rules: {
            class_name : {
            	required : true,
            	maxlength: 28, 
            },
            countTimestart: {
                required: true
            }, 
            countTimeend: {
                required: true
            }, 
            subject_id: {
                required: true
            },
            course_id: {
                required: true
            }, 
            teacher_id: {
                required: true
            },           
        },
        onkeyup:false,
        success:"valid",
        submitHandler:function(form){
            layer.confirm('确定提交吗?',function(){
                $(form).ajaxSubmit({
                    type : 'POST',
                    url  : 'addClasses',
                    typeData : 'JSON',
                    success : function (result){
                        if(result.code == 0){
                            layer.alert(result.msg,{icon:1,closeBtn:0},function(){
                                window.parent.location.href = window.parent.location.href;
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