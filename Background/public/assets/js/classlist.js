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
        "aoclassDefs": [
            {"orderable":false,"aTargets":[]}// 制定列不参与排序
        ],
});
    /*class-添加*/
    function class_add(){
        var index = layer.open({
            type: 2,
            title: '添加班级信息',
            content: 'classadd',
            end: function(){
                location.replace(location.href);
            }
        });
        layer.full(index);
    }
    /*class-修改*/
    function class_edit(id){
        var index = layer.open({
            type: 2,
            title: '班级信息修改',
            content: 'classedit/class_id/'+id,
            end: function(){
                location.replace(location.href);
            }
        });
        layer.full(index);
    }
    /*class-展示*/
    function class_start(obj,id){
        layer.confirm('确认开班吗？',function(index){
            var str = {id : id};
            $.post("startClasses", str, function (res) {
                if (res.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius" title="已开班" onClick="class_stop(this,'+id+');" style="cursor: pointer;">已开班</span>');
                    $(obj).remove();
                    layer.msg('已开班!',{icon: 6,time:1000});
                } else {
                    layer.msg('操作失败!',{icon: 5,time:1000});
                }
            })
        });
    }
    /*class-不展示*/
    function class_stop(obj,id){
        layer.confirm('确认不开班吗？',function(index){
            var str = {id : id};
            $.post("stopClasses", str, function (res) {
                if (res.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius" title="未开班" onClick="class_start(this,'+id+');" style="cursor: pointer;">未开班</span>');
                    $(obj).remove();
                    layer.msg('未开班!',{icon: 6,time:1000});
                } else {
                    layer.msg('操作失败!',{icon: 5,time:1000});
                }
            })
        });
    }
    /*class-删除*/
    function class_del(obj,id) {
        layer.confirm('确认要删除班级吗？', function (index) {
            var str = {id: id};
            $.post("delClasses", str, function (res) {
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
    /*class-详情*/
    function class_detail(id){
        var index = layer.open({
            type: 2,
            title: '班级详情',
            content: 'classdetail/class_id/'+id,
            end: function(){
                location.replace(location.href);
            }
        });
        layer.full(index);
    }     