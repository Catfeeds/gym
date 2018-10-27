//删除科目
function subject_delete(obj, subject_id) {
    layer.confirm('确认要删除科目与对应的课程，班级信息吗？', function (index) {
        var data = { 'subject_id': subject_id };
        $.ajax({
            type: 'POST',
            url: '/index/course/deleteSubject',
            data: data,
            typeData: 'JSON',
            success: function (result) {
                if (result.code < 400) {
                    $(obj).parents("tr").remove();
                    layer.msg('已删除!', { icon: 1, time: 1000 });
                } else {
                    layer.alert(result.msg, { icon: 2, closeBtn: 0 }, function () {
                        window.location.href = window.location.href;
                    });
                }
            },
            error: function () {
                layer.alert('请求失败!', { icon: 2 });
            }
        });
    });
}
//添加科目
function subject_public(url, subject_id, obj) {
    $("input[type='hidden']").remove();
    $('#subject_name').val('');
    $('#form-public').attr('action', url);
    if (subject_id) {
        var subject_id = $('<input type="hidden" name="subject_id" value="' + subject_id + '"/>');
        $('#form-public').append(subject_id);
        var subject_name = $(obj).parent().parent().find('td:nth-child(2)').html();
        $('#subject_name').val(subject_name);
    }
    $("#modal-demotwo").modal("show");
}
//发送添加或编辑请求
function subjectSendChange() {
    var lt = $('#subject_name').val();
    if (lt > 30 || lt.length == 0) {
        layer.alert('请填写科目名称,字数不能超过30个字符.');
        return;
    }
    var url = $('#form-public').attr('action');
    $('#form-public').ajaxSubmit({
        type: 'post',
        url: url,
        typeData: 'JSON',
        success: function (result) {
            if (result.code < 400) {
                layer.alert(result.msg, { icon: 1, closeBtn: 0 }, function () {
                    window.location.href = window.location.href;
                });
            } else {
                layer.alert(result.msg);
            }
        },
        error: function () {
            layer.alert('请求失败!', { icon: 2 });
        }
    });
}
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
    "aaSorting": [[0, "desc"]],//默认第几个排序
    "bStateSave": true,//状态保存
    "pading": false,
    "aoColumnDefs": [
        //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
        { "orderable": false, "aTargets": [0, 1, 2] }// 不参与排序的列
    ],
});