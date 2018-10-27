/*教师添加*/
function add_teacher() {
    var index = layer.open({
        type: 2,
        title: '添加教师',
        content: '/index/course/addteacher',
        end: function () {
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*教师编辑*/
function edit_teacher(teacher_id) {
    var index = layer.open({
        type: 2,
        title: '编辑教师信息',
        content: '/index/course/editteacher/teacher_id/' + teacher_id,
        end: function () {
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*教师离职改为在职*/
function teacher_restart(obj, id) {
    layer.confirm('确认更改吗？', function (index) {
        var str = { teacher_id: id };
        $.post("restartTeacher", str, function (res) {
            if (res.code == 0) {
                $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius" title="在职" onClick="teacher_stop(this,' + id + ');" style="cursor: pointer;">在职</span>');
                layer.msg(res.msg, { icon: 6, time: 1300 });
            } else {
                layer.msg(res.msg, { icon: 5, time: 1300 });
            }
        })
    });
}
/*教师在职改为离职*/
function teacher_stop(obj, id) {
    layer.confirm('确认要更改吗？', function (index) {
        $.ajax({
            type: 'POST',
            url: 'stopTeacher',
            data: { teacher_id: id },
            typeData: 'JSON',
            success: function (res) {
                if (res.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius" title="已离职" onClick="teacher_restart(this,' + id + ');" style="cursor: pointer;">已离职</span>');
                    layer.msg(res.msg, { icon: 6, time: 1300 });
                }
                if (res.code == 400) {
                    layer.msg(res.msg, { icon: 5, time: 1300 });
                }
            },
            error: function () {
                layer.alert('请求失败!', { icon: 2 });
            }
        });
    });
}
/*teacher-删除*/
function teacher_del(obj, id) {
    layer.confirm('确认要删除吗？', function (index) {
        $.ajax({
            type: 'POST',
            url: 'deleteTeacher',
            data: { teacher_id: id },
            typeData: 'JSON',
            success: function (res) {
                if (res.code == 0) {
                    $(obj).parents("tr").remove();
                    layer.msg(res.msg, { icon: 6, time: 1300 });
                }
                if (res.code == 400) {
                    layer.msg(res.msg, { icon: 5, time: 1300 });
                }
            },
            error: function () {
                layer.alert('请求失败!', { icon: 2 });
            }
        });
    });
}
/*teacher-详情*/
function teacher_detail(id) {
    var index = layer.open({
        type: 2,
        title: '教师班级信息',
        content: 'teacherdetail/teacher_id/' + id,
        end: function () {
            location.replace(location.href);
        }
    });
    layer.full(index);
}
$(function () {
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
        ]
    });
});
// 上传excel文件
$('#excel').change(function (event) {
    var formData = new FormData();
    formData.append("file", $(this).get(0).files[0]);
    // 前端正则文件类型
    var file = $(this).val();
    var arr = file.split('\\');//注split可以用字符或字符串分割
    var fileName = arr[arr.length - 1];//这就是要取得的名文件称
    if ($('#upload').val() == "") {
        layer.msg("请选择所要上传的文件", { icon: 5, time: 1300 });
    } else {
        var index = file.lastIndexOf(".");
        if (index < 0) {
            layer.msg("文件格式不正确，请选择模板Excel文件！", { icon: 5, time: 1300 });
        } else {
            var ext = file.substring(index + 1, file.length);
            if (ext == "xls" || ext == "xlsx") {
                startUploadFile(formData);
            } else {
                layer.msg("文件格式不正确，请选择模板Excel文件！", { icon: 5, time: 1300 });
            }
        }
    }
});
// 上传文件
function startUploadFile(formData) {
    // loading层 0.1透明度
    var index = layer.load(1, { shade: [0.9, '#fff'] });
    $.ajax({
        url: 'uploadExcel',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,    //不可缺
        processData: false,    //不可缺
        success: function (res) {
            // 关闭loading层
            layer.close(index);
            if (res.code == 0) {
                layer.msg(res.msg, { icon: 1, time: 1000 });
                // 调用方法
                showConfirm();
            }
            if (res.code == 400) {
                layer.msg(res.msg, { icon: 2, time: 1000 });
            }
        },
        error: function (res) {
            layer.msg('出错！请刷新！', { icon: 2, time: 1000 });
        },
        complete: function () {
            layer.close(index);
        }
    });
}
// 导入excel
function showConfirm() {
    layer.confirm('确认导入教师信息吗？', function (index) {
        // loading层 0.1透明度
        var index2 = layer.load(1, { shade: [0.9, '#fff'] });
        // type为1时为导出用户信息
        var type = 2;
        $.ajax({
            url: 'importExcel',
            type: 'POST',
            dataType: 'json',
            data: { type: type },
            success: function (res) {
                // 关闭loading层
                layer.close(index);
                if (res.code == 0) {
                    layer.msg(res.msg, { icon: 1, time: 1000 });
                    // 刷新页面
                    setTimeout("location.replace(location.href);", 1000);
                } else {
                    layer.msg(res.msg, { icon: 2, time: 2000 });
                }
            },
            error: function (res) {
                layer.msg('错误，请稍后再试！', { icon: 2, time: 1000 });
            },
            complete: function () {
                layer.close(index2);
            }
        });
    });
}
// 下载excel模板
function excel_download() {
    layer.confirm('确认下载模板吗？', function (index) {
        // loading层 0.1透明度
        var index2 = layer.load(1, { shade: [0.9, '#fff'] });
        // type为1时为导出用户信息
        var type = 2;
        $.ajax({
            url: 'downTemplate',
            type: 'POST',
            dataType: 'json',
            data: { type: type },
            success: function (res) {
                // 关闭loading层
                layer.close(index);
                if (res.code == 0) {
                    layer.open({
                        type: 1,
                        area: ['300px', '200px'],
                        fix: false, //不固定
                        maxmin: true,
                        shade: 0.4,
                        title: '信息',
                        content: '<div style="text-align:center;margin-top:50px;"><a style="color:red;cursor:pointer;font-size:20px;" class="downloadPath" href="' + res.data + '">请点击下载模板文件</a></div>'
                    });
                    // layer.msg(res.msg, { icon: 1, time: 1300 });
                }
                if (res.code == 400) {
                    layer.msg(res.msg, { icon: 2, time: 1000 });
                }
            },
            error: function (res) {
                layer.msg('错误，请稍后再试！', { icon: 2, time: 1000 });
            },
            complete: function () {
                layer.close(index2);
            }
        });
    });
}   