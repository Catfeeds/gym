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
    /*msg-添加*/
    function msg_add(){
        var index = layer.open({
            type: 2,
            title: '添加公告信息',
            content: 'tomsgadd',
            end: function(){
                location.replace(location.href);
            }
        });
        layer.full(index);
    }
    /*msg-修改*/
    function msg_edit(id){
        var index = layer.open({
            type: 2,
            title: '公告信息修改',
            content: 'tomsgedit/msg_id/'+id,
            end: function(){
                location.replace(location.href);
            }
        });
        layer.full(index);
    }
    /*msg-发送*/
    function msg_start(obj,id){
        layer.confirm('确认发送吗？',function(index){
            var str = {id : id};
            $.post("startMsg", str, function (res) {
                if (res.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius" title="发送" onClick="msg_stop(this,'+id+');" style="cursor: pointer;">已发送</span>');
                    $(obj).remove();
                    layer.msg('已发送!',{icon: 6,time:1000});
                } else {
                    layer.msg('操作失败!',{icon: 5,time:1000});
                }
            })
        });
    }
    /*msg-不发送*/
    // function msg_stop(obj,id){
    //     layer.confirm('确认不发送吗？',function(index){
    //         var str = {id : id};
    //         $.post("stopMsg", str, function (res) {
    //             if (res.code == 0) {
    //                 $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius" title="不发送" onClick="msg_start(this,'+id+');" style="cursor: pointer;">不发送</span>');
    //                 $(obj).remove();
    //                 layer.msg('不发送!',{icon: 6,time:1000});
    //             } else {
    //                 layer.msg('操作失败!',{icon: 5,time:1000});
    //             }
    //         })
    //     });
    // }
    /*msg-删除*/
    function msg_del(obj,id) {
        layer.confirm('确认要删除吗？', function (index) {
            var str = {id: id};
            $.post("delMsg", str, function (res) {
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
