/*反馈回复*/
function feedbackReply(obj,idx,status)
{   
    var message = $(obj).parents('tr').find('td:nth-child(4)').html();
    $('#message').val(message);
    $('#reply').val('');
    $('#idx').val('');
    $('#status').val('');
    $('#idx').val(idx);
    $('#status').val(status);
    $("#modal-demotwo").modal("show");
}
/*字数限制*/
function textarealength(obj,number){
    var value = $(obj).val();
    if( value.length > number ){
        value = value.substring(0,number);
        $(obj).val(value);
    }
    $(obj).next().find('em').html(value.length);
}
/**
 * 发送回复内容
 */
function sendFeedbackReply(){
    var lt = $('#reply').val().length;
    if( lt > 140 || lt == 0 ){
        layer.alert('请填写回复内容,字数不能超过140个字符.');
        return;
    }
    layer.confirm('确定回复?',function(){
        $('#form-feedback-rebly').ajaxSubmit({
            type : 'POST',
            url  : '/index/system/replyfeedback',
            typeData : 'JSON',
            success : function (result){
                if( result.code < 400 ){
                    layer.alert(result.msg,{icon:1,closeBtn:0},function(){
                        window.location.href = window.location.href;
                    });
                }else{
                    layer.alert(result.msg,{icon:2});
                }
            },
            error : function(){
                layer.alert('请求失败!',{icon:2});
            }
        });
    });
}
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
        "aaSorting": [[ 0, "desc" ]],//默认第几个排序
        "bStateSave": true,//状态保存
        "aoColumnDefs": [
            {"orderable":false,"aTargets":[]}// 制定列不参与排序
        ]
    });
});