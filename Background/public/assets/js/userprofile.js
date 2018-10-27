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
        "aaSorting": [[ 0, "asc" ]],//默认第几个排序
        "bStateSave": true,//状态保存
        "aoColumnDefs": [
            {"orderable":false,"aTargets":[]}// 制定列不参与排序
        ],
}); 
// 点击切换
function switch_phone1(obj){
    $(obj).parents("td").find(".phone1").hide();
    $(obj).parents("td").find(".phone2").show();
}
function switch_phone2(obj){
    $(obj).parents("td").find(".phone1").show();
    $(obj).parents("td").find(".phone2").hide();    
}
/*user-添加*/
function user_add(){
    var index = layer.open({
        type: 2,
        title: '添加学生信息',
        content: 'useradd',
        end: function(){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*user-修改*/
function user_edit(id){
    var index = layer.open({
        type: 2,
        title: '学生信息修改',
        content: 'useredit/uid/'+id,
        end: function(){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*user-详情*/
function user_detail(id){
    var index = layer.open({
        type: 2,
        title: '学生详情',
        content: 'userdetail/uid/'+id,
        end: function(){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*user-修改*/
function user_course(id, obj){
    var index = layer.open({
        type: 2,
        title: '学生课程信息',
        content: 'usercourse/uid/'+id,
        success: function(layero, index){
            var body = layer.getChildFrame('body',index);//建立父子联系
            var username = $(obj).parents('tr').find('#username').text();
            body.find('#username').text('学生姓名： ' + username);
        },
        end: function(){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*user-展示*/
// function user_start(obj,id){
//     layer.confirm('确认更改吗？',function(index){
//         var str = {id : id};
//         $.post("startUser", str, function (res) {
//             if (res.code == 0) {
//                 $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius" title="已认证" onClick="user_stop(this,'+id+');" style="cursor: pointer;">已认证</span>');
//                 $(obj).remove();
//                 layer.msg('更改成功!',{icon: 6,time:1000});
//             } else {
//                 layer.msg('操作失败!',{icon: 5,time:1000});
//             }
//         })
//     });
// }
/*user-不展示*/
// function user_stop(obj,id){
//     layer.confirm('确认更改吗？',function(index){
//         var str = {id : id};
//         $.post("stopUser", str, function (res) {
//             if (res.code == 0) {
//                 $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius" title="未认证" onClick="user_start(this,'+id+');" style="cursor: pointer;">未认证</span>');
//                 layer.msg('更改成功!',{icon: 6,time:1000});
//             } else {
//                 layer.msg('操作失败!',{icon: 5,time:1000});
//             }
//         })
//     });
// }
/*user-删除*/
function user_del(obj,id) {
    layer.confirm('确认要删除该学生和对应的班级，课程信息吗？', function (index) {
        var str = {id: id};
        $.post("delUser", str, function (res) {
            if (res.code == 0) {
                $(obj).parents("tr").remove();
                $(obj).remove();
                layer.msg(res.msg, {icon: 6, time: 1000});
            } else {
                layer.msg(res.msg, {icon: 5, time: 1000});
            }
        })
    });
}

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
    layer.confirm('确认导入学生信息吗？', function (index) {
        // loading层 0.1透明度
        var index2 = layer.load(1, { shade: [0.9, '#fff'] });
        // type为1时为导出用户信息
        var type = 1;
        $.ajax({
            url: 'importExcel',
            type: 'POST',
            dataType: 'json',
            data: {type:type},
            success: function (res) {
                // 关闭loading层
                layer.close(index);
                if (res.code == 0) {
                    layer.msg(res.msg, { icon: 1, time: 1000 });
                    // 刷新页面
                    setTimeout("location.replace(location.href);",1000);
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

// 下载excel模板
function excel_download() {
    layer.confirm('确认下载模板吗？', function (index) {
        // loading层 0.1透明度
        var index2 = layer.load(1, { shade: [0.9, '#fff'] });
        // type为1时为导出用户信息
        var type = 1;
        $.ajax({
            url: 'downTemplate',
            type: 'POST',
            dataType: 'json',
            data: {type:type},
            success: function (res) {
                // 关闭loading层
                layer.close(index);
                if (res.code == 0) {
                    // layer.msg(res.msg, { icon: 1, time: 1300 });
                    layer.open({
                        type: 1,
                        area: ['300px','200px'],
                        fix: false, //不固定
                        maxmin: true,
                        shade:0.4,
                        title: '信息',
                        content: '<div style="text-align:center;margin-top:50px;"><a style="color:red;cursor:pointer;font-size:20px;" class="downloadPath" href="'+res.data+'">请点击下载模板文件</a></div>'
                    });                    
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