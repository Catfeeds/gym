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

    /*添加*/
    function admin_add(){
        var index = layer.open({
            type: 2,
            title: '添加管理员',
            content: 'adminadd',
            end: function(){
                location.replace(location.href);
            }
        });
        layer.full(index);
    }

    /*admin-启用*/
    function admin_start(obj,id){
        layer.confirm('确认启用吗？',function(index){
            var str = {id : id};
            $.post("startadmin", str, function (data) {
                if (data.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius" title="启用" onClick="admin_stop(this,'+id+');" style="cursor: pointer;">已启用</span>');
                    $(obj).remove();
                    layer.msg(data.msg,{icon: 6,time:1000});
                } else {
                    layer.msg(data.msg,{icon: 5,time:1000});
                }
            })
        });
    }

    /*admin-不启用*/
    function admin_stop(obj,id){
        layer.confirm('确认不启用吗？',function(index){
            var str = {id : id};
            $.post("stopadmin", str, function (data) {
                if (data.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius" title="不启用" onClick="admin_start(this,'+id+');" style="cursor: pointer;">不启用</span>');
                    $(obj).remove();
                    layer.msg(data.msg,{icon: 6,time:1000});
                } else {
                    layer.msg(data.msg,{icon: 5,time:1000});
                }
            })
        });
    }

    /*admin-删除*/
    function admin_del(obj,id) {
        layer.confirm('确认要删除吗？', function (index) {
            var str = {id: id};
            $.post("deladmin", str, function (data) {
                if (data.code == 0) {
                    $(obj).parents("tr").remove();
                    $(obj).remove();
                    layer.msg(data.msg, {icon: 6, time: 1000});
                    setTimeout('window.location.reload();',1000);//刷新页面
                } else {
                    layer.msg(data.msg, {icon: 5, time: 1000});
                }
            })
        });
    }

    // 测评修改
    function admin_edit(id){
        var index = layer.open({
            type: 2,
            title: '修改管理员',
            content: 'adminedit/admin_id/'+id,
            end: function(){
                location.replace(location.href);
            }            
        });
        layer.full(index);
    }