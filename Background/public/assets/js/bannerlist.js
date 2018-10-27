    $(function(){
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
            ]
        });
    });
    /*banner-添加*/
    function banner_add(title,url){
        var index = layer.open({
            type: 2,
            title: title,
            content: url,
            end: function(){
                location.replace(location.href);
            }
        });
        layer.full(index);
    }
    /*banner-启用*/
    function banner_start(obj,id){
        layer.confirm('确认展示吗？',function(index){
            var str = {id : id};
            $.post("bannerStart", str, function (res) {
                if (res.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius" title="展示" onClick="banner_stop(this,'+id+');" style="cursor: pointer;">已展示</span>');
                    $(obj).remove();
                    layer.msg('已展示!',{icon: 6,time:1000});
                } else {
                    layer.msg('操作失败!',{icon: 5,time:1000});
                }
            })
        });
    }
    /*banner-不展示*/
    function banner_stop(obj,id){
        layer.confirm('确认不展示吗？',function(index){
            var str = {id : id};
            $.post("bannerStop", str, function (res) {
                if (res.code == 0) {
                    $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius" title="不展示" onClick="banner_start(this,'+id+');" style="cursor: pointer;">不展示</span>');
                    $(obj).remove();
                    layer.msg('已不展示!',{icon: 6,time:1000});
                } else {
                    layer.msg('操作失败!',{icon: 5,time:1000});
                }
            })
        });
    }
    /*banner-删除*/
    function banner_del(obj,id) {
        layer.confirm('确认要删除吗？', function (index) {
            var str = {id: id};
            $.post("bannerDel", str, function (res) {
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
    /*banner-排序修改*/
    function banner_edit(id){
        $("#modal-demotwo").modal("show");
        $("#sort_id").val(id);
    }
    /*banner-排序修改提交*/
    function banner_sort_edit(){
        var sort_id = $("#sort_id").val();
        var banner_sort = $("#banner_sort").val();
        if(banner_sort == ''){
            layer.msg('排序不能为空哦！', {icon: 5, time: 1000});
            return false;
        }
        var str = {id: sort_id,banner_sort:banner_sort};
        $.post("bannerSortEdit", str, function (res) {
            if (res.code == 0) {
                layer.alert(res.msg,{icon:1,closeBtn:0},function(){
                    window.location.href = window.location.href;
                });                
            } else {
                layer.msg(res.msg, {icon: 5, time: 1000});
            }
        })        
    }