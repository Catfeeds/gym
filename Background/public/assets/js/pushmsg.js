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
        "aaSorting": [[ 0, "desc" ]],//默认第几个排序
        "bStateSave": true,//状态保存
        "aomsgDefs": [
            {"orderable":false,"aTargets":[]}// 制定列不参与排序
        ],
});
/*pushmsg-添加*/
function pushmsg_add(){
    var index = layer.open({
        type: 2,
        title: '添加推送信息',
        content: 'pushmsgadd',
        end: function(){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*pushmsg-修改*/
function pushmsg_edit(id){
    var index = layer.open({
        type: 2,
        title: '推送信息修改',
        content: 'pushmsgedit/pushmsg_id/'+id,
        end: function(){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*pushmsg-发送*/
function pushmsg_start(obj,id){
    layer.confirm('确认发送吗？',function(index){
        var str = {id : id};
        $.post("startPushmsg", str, function (res) {
            if (res.code == 0) {
                $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius" title="发送" onClick="pushmsg_stop(this,'+id+');" style="cursor: pointer;">已发送</span>');
                $(obj).remove();
                layer.msg('已发送!',{icon: 6,time:1000});
            } else {
                layer.msg('操作失败!',{icon: 5,time:1000});
            }
        })
    });
}
/*pushmsg-不发送*/
// function pushmsg_stop(obj,id){
//     layer.confirm('确认不发送吗？',function(index){
//         var str = {id : id};
//         $.post("stopPushmsg", str, function (res) {
//             if (res.code == 0) {
//                 $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius" title="不发送" onClick="pushmsg_start(this,'+id+');" style="cursor: pointer;">不发送</span>');
//                 $(obj).remove();
//                 layer.msg('不发送!',{icon: 6,time:1000});
//             } else {
//                 layer.msg('操作失败!',{icon: 5,time:1000});
//             }
//         })
//     });
// }
/*pushmsg-删除*/
function pushmsg_del(obj,id) {
    layer.confirm('确认要删除吗？', function (index) {
        var str = {id: id};
        $.post("delPushmsg", str, function (res) {
            if (res.code == 0) {
                $(obj).parents("tr").remove();
                $(obj).remove();
                layer.msg('删除成功!', {icon: 6, time: 1000});
            } else {
                layer.msg('操作失败!', {icon: 5, time: 1000});
            }
        })
    });
}