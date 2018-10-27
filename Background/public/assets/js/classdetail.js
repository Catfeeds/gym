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
    "aaSorting": [[ 1, "asc" ]],//默认第几个排序
    "bStateSave": true,//状态保存
    "pading":false,
    "aoColumnDefs": [
        //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
        {"orderable":false,"aTargets":[0,1,2]}// 不参与排序的列
    ],
});
// 点击全选checkbox
$(".allcheck").click(function(){
    if(this.checked==true){
        $("input[type=checkbox]").prop("checked",true);
    }else{
        $("input[type=checkbox]").prop("checked",false);
    }
});
// 批量打卡
function multi_clock(classId){
    var select = "";
    $('input[name="id"]:checked').each(function(){
        select+=$(this).val()+"*";//向数组中添加元素
    });
    if (select == "") {
        layer.msg('请至少选择一个!',{icon:2,time:1300});
    }else{
        $("#modal-demo").modal("show");
        $('#class_id').text(classId);
        $('#uid').text(select);
        $('#type').text(1);
        // 调用打卡方法
        // clock(type,classId,clockAt,uid,clockType);
    }   
}
/*user-删除*/
function user_del(obj,id) {
    layer.confirm('确认要删除吗？', function (index) {
        var str = {id: id};
        $.post("../../delUser", str, function (res) {
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
// 日期选择
function selecttime(){   
    var startTime = $("#countTimestart").val();

    var myDate = new Date();    
    var year = myDate.getFullYear();   //获取完整的年份(4位,1970-????)
    var month = myDate.getMonth()+1;   //获取当前月份(1-12)
    var day = myDate.getDate();        //获取当前日(1-31)
    //获取完整年月日
    var newDay = year + "-" + month + "-" + day;

    if(startTime != "" || !startTime){
        WdatePicker({dateFmt:'yyyy-MM-dd HH:mm', minDate:'',maxDate:newDay})
    }else{
        WdatePicker({dateFmt:'yyyy-MM-dd HH:mm', minDate:'',maxDate:newDay})
    }
}
// 点击打卡
function course_clock(uid, classId){
    $("#modal-demo").modal("show");
    $('#class_id').text(classId);
    $('#uid').text(uid);
    $('#type').text(0);
}
// 打卡方法
$("#confirm").click(function(){
    var classId = $('#class_id').text();
    var clockAt = $("#countTimestart").val();
    var uid = $('#uid').text();
    var clockType = $("#clock_type").val();
    var type = $('#type').text();
    // console.log(clockTime);
    if($("#countTimestart").val() ==''){
        layer.msg('请选择打卡时间！', { icon: 2, time: 1300 });
        return false;
    }
    // 调用打卡方法
    clock(type,classId,clockAt,uid,clockType);
});
// 打卡公共方法
function clock(type,classId,clockAt,uid,clockType){
    // loading层 0.1透明度
    var index = layer.load(1,{shade:[0.9,'#fff']});
    setTimeout(function(){            
        // 发送ajax
        $.ajax({
            type: 'POST',
            url: '../../clockIn',
            dataType: 'json',
            data: {type:type,classId:classId,clockAt:clockAt,uid:uid,clockType:clockType},
            success: function (res) {
                if (res.code == 0) {
                    layer.close(index);
                    layer.msg(res.msg, { icon: 1, time: 1300 });
                    $("#modal-demo").modal("hide");
                }else{
                    layer.close(index);
                    layer.msg(res.msg, { icon: 2, time: 1300 });
                }
            },
            error: function () {
                layer.msg('请求错误！请稍后再试！', { icon: 2, time: 1300 });
            },
            complete: function () {
                layer.close(index);
                setTimeout(function(){location.replace(location.href);}, 1300);
            },
        }); 
    },600);  
}