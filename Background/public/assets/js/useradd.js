// 页面加载后执行
window.onload = function () {
    if (!$("#subject_id").val()) {
        layer.alert('没有科目信息，请添加科目！', { icon: 1, closeBtn: 0 }, function () {
            // var index = layer.load();
            // layer.close(index);
            window.parent.location.href = window.parent.location.href;

        });
    }
}
/*字数限制*/
function textarealength(obj, number) {
    var value = $(obj).val();
    if (value.length > number) {
        value = value.substring(0, number);
        $(obj).val(value);
    }
    $(obj).next().find('em').html(value.length);
}
// 日期选择
function selecttime() {
    var startTime = $("#countTimestart").val();
    if (startTime != "" || !startTime) {
        WdatePicker({ dateFmt: 'yyyy-MM-dd', minDate: '', maxDate: '' });
    } else {
        WdatePicker({ dateFmt: 'yyyy-MM-dd', minDate: '', maxDate: '' });
    }
}
// // 日期选择2
function selecttime1() {
    var endTime = $("#countTimeend").val();
    if (endTime != "" || !endTime) {
        WdatePicker({ dateFmt: 'yyyy-MM-dd', minDate: '', maxDate: '' });
    } else {
        WdatePicker({ dateFmt: 'yyyy-MM-dd', minDate: '', maxDate: '' });
    }
}
// 选择框事件
$('#subject_id').change(function () {
    getSubjectCourse();
});
$('#course_id').change(function () {
    getCourseClass();
    getCourseTimes();
});
// 调用方法
getSubjectCourse();
// 请求科目对应课程
function getSubjectCourse() {
    $.ajax({
        type: 'POST',
        url: 'getSubjectCourse',
        dataType: 'json',
        data: { subject_id: $('#subject_id').val() },
        success: function (res) {
            if (res.code == 0) {
                $('#course_id option').remove();
                for (var i = 0; i < res.data.length; i++) {
                    var option = $('<option value="' + res.data[i].course_id + '">' + res.data[i].course_name + '</option>');
                    $('#course_id').append(option);
                }
            }
            if (res.code == 400) {
                $('#course_id option').remove();
                layer.alert(res.msg, { icon: 2 });
            }
            // 请求课程对应班级           
            getCourseClass();
            // 请求课程对应打卡次数与结束时间  
            $('#course_times').val('');
            $('#course_period').val('');
            getCourseTimes();
        },
        error: function () {
            layer.msg('请求错误，请稍后重试！', { icon: 2, time: 1000 });
        },
    });
}
// 请求课程对应班级
function getCourseClass() {
    $.ajax({
        type: 'POST',
        url: 'getCourseClass',
        dataType: 'json',
        data: { course_id: $('#course_id').val() },
        success: function (res) {
            if (res.code == 0) {
                $('#class_id option').remove();
                for (var i = 0; i < res.data.length; i++) {
                    var option = $('<option value="' + res.data[i].class_id + '">' + res.data[i].class_name + '</option>');
                    $('#class_id').append(option);
                }
            }
            if (res.code == 400) {
                $('#class_id option').remove();
                // layer.msg(res.msg, { icon: 2, time: 1000 });
            }
        },
        error: function () {
            layer.msg('请求错误，请稍后重试！', { icon: 2, time: 1000 });
        },
    });
}
// 请求课程对应打卡次数与结束时间
function getCourseTimes() {
    $.ajax({
        type: 'POST',
        url: 'getCourseTimes',
        dataType: 'json',
        data: { course_id: $('#course_id').val() },
        success: function (res) {
            if (res.code == 0) {
                $('#course_times').val(res.data.course_times);
                $('#course_period').val(res.data.course_period);
            }
            if (res.code == 400) {
                // layer.msg(res.msg, { icon: 2, time: 1000 });
            }
        },
        error: function () {
            layer.msg('请求失败，请稍后重试！', { icon: 2, time: 1000 });
        },
    });
}
// 表单正则
$(function () {
    jQuery.validator.addMethod('isMobile', function (value, element) {
        var length = value.length;
        var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
        return (length == 11 && mobile.test(value));
    }, "请输入正确的手机号码");
    /*表单提交*/
    $("#form-class-add").validate({
        rules: {
            user_name: {
                required: true,
                maxlength: 28,
            },
            user_no: {
                required: true,
                maxlength: 18,
                digits: true
            },
            user_phone: {
                required: true,
                maxlength: 11,
                digits: true,
                isMobile: true
            },
            course_left_times: {
                required: true,
                maxlength: 10,
                digits: true
            },
            countTimestart: {
                required: true,
                maxlength: 12,
            },
            countTimeend: {
                required: true,
                maxlength: 12,
            },
            subject_id: {
                required: true
            },
            course_id: {
                required: true
            },
            class_id: {
                required: true
            }
        },
        onkeyup: false,
        success: "valid",
        submitHandler: function (form) {
            layer.confirm('确定提交吗?', function () {
                $(form).ajaxSubmit({
                    type: 'POST',
                    url: 'addUser',
                    typeData: 'JSON',
                    success: function (result) {
                        if (result.code == 0) {
                            layer.alert(result.msg, { icon: 1, closeBtn: 0 }, function () {
                                window.parent.location.href = window.parent.location.href;
                            });
                        }else{
                            layer.msg(result.msg, { icon: 2, time: 2000 });
                        }
                    },
                    error: function () {
                        layer.alert('请求失败!', { icon: 2 });
                    }
                });
            });
        }
    });
});