// 初始化表格
$('.table-sort').dataTable({
    language: {
        "sProcessing": "处理中...",
        "sLengthMenu": "显示 _MENU_ 项结果",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
        "sInfoPostFix": "",
        "sSearch": "从当前数据中检索： ",
        "sUrl": "",
        "sEmptyTable": "表中数据为空",
        "sLoadingRecords": "载入中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首页",
            "sPrevious": "上页",
            "sNext": "下页",
            "sLast": "末页"
        },
        "oAria": {
            "sSortAscending": ": 以升序排列此列",
            "sSortDescending": ": 以降序排列此列"
        }
    },
    "aaSorting": [[0, "asc"]],//默认第几个排序
    "bStateSave": true,//状态保存
    "aoColumnDefs": [
        { "orderable": false, "aTargets": [] }// 制定列不参与排序
    ],
});

// 课程续费
function course_renew(obj, uid, classId) {
    layer.confirm('确认要续费吗？', function (index) {
        var str = { uid: uid, classId: classId };
        $.post("../../courseRenew", str, function (res) {
            if (res.code == 0) {
                layer.msg(res.msg, { icon: 6, time: 1000 });
                setTimeout("location.replace(location.href);", 1000);
            } else {
                layer.msg(res.msg, { icon: 5, time: 1000 });
            }
        })
    });
}

//添加科目
function course_add(url) {
    $('#form-public').attr('action', url);
    $("#modal-demotwo").modal("show");
}

// 课程删除
function course_delete(obj, uid, classId) {
    layer.confirm('确认要删除当前课程吗？', function (index) {
        var data = { uid: uid, classId: classId };
        $.post("../../delUserCourse", data, function (res) {
            if (res.code == 0) {
                layer.msg(res.msg, { icon: 6, time: 1000 });
                setTimeout("location.replace(location.href);", 1000);
            } else {
                layer.msg(res.msg, { icon: 5, time: 1000 });
            }
        })
    });
}

// 日期选择2
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
        url: '../../getSubjectCourse',
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
                layer.msg(res.msg, { icon: 2, time: 1000 });
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
        url: '../../getCourseClass',
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
                layer.msg(res.msg, { icon: 2, time: 1000 });
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
        url: '../../getCourseTimes',
        dataType: 'json',
        data: { course_id: $('#course_id').val() },
        success: function (res) {
            if (res.code == 0) {
                $('#course_times').text(res.data.course_times);
                $('#course_period').text(res.data.course_period);
            }
            if (res.code == 400) {
                layer.msg(res.msg, { icon: 2, time: 1000 });
            }
        },
        error: function () {
            layer.msg('请求失败，请稍后重试！', { icon: 2, time: 1000 });
        },
    });
}

// 表单正则
$(function () {
    /*表单提交*/
    $('#submit').click(function () {

        // 简单验证
        if ($('#subject_id').val() == "") {
            layer.alert('科目不能为空!', { icon: 2 });
            return;
        }
        if ($('#course_id').val() == "") {
            layer.alert('课程不能为空!', { icon: 2 });
            return;
        }
        if ($('#class_id').val() == "") {
            layer.alert('班级不能为空!', { icon: 2 });
            return;
        }

        layer.confirm('确定提交吗?', function () {
            $.ajax({
                type: 'POST',
                url: '../../addUserCourse',
                dataType: 'json',
                data: {
                    uid: $('#uid').text(),
                    subjectid: $('#subject_id').val(),
                    classid: $('#class_id').val(),
                    courseid: $('#course_id').val()
                },
                success: function (res) {
                    console.log(res)
                    if(res.code == 0){
                        layer.msg('课程新增成功', { icon: 1, time: 1000 });
                        
                        // $('.close-btn').click();
                        setTimeout("location.replace(location.href);", 1000);
                    }else{
                        layer.msg(res.msg, { icon: 2, time: 1000 });
                    }
                },
                error: function (res) {
                    layer.msg('请求失败，请稍后重试！', { icon: 2, time: 1000 });
                },
            });
        });
    })
});