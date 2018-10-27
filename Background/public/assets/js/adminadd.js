// zTree插件
var setting = {
    check: {
        enable: true
    },
    data: {
        simpleData: {
            enable: true,
            idKey: "id",
            pIdKey: "pId",
            rootPId: 0
        }
    },
    callback:{
        beforeCheck:true,
        onCheck:onCheck
    }
};
var code;
function setCheck() {
    var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
    py = $("#py").attr("checked")? "p":"p",
    sy = $("#sy").attr("checked")? "s":"s",
    pn = $("#pn").attr("checked")? "p":"p",
    sn = $("#sn").attr("checked")? "s":"s",
    type = { "Y":py + sy, "N":pn + sn};
    zTree.setting.check.chkboxType = type;
    showCode('setting.check.chkboxType = { "Y" : "' + type.Y + '", "N" : "' + type.N + '" };');
}
function showCode(str) {
    if (!code) code = $("#code");
    code.empty();
    code.append("<li>"+str+"</li>");
}
var aa='';
function onCheck(e,treeId,treeNode){
    var treeObj=$.fn.zTree.getZTreeObj("treeDemo"),
    nodes=treeObj.getCheckedNodes(true),
    v="";
    for(var i=0;i<nodes.length;i++){
        v+=nodes[i].id + ",";
        // console.log("节点id:"+nodes[i].id+"节点名称"+v); //获取选中节点的值
    }
    aa=v;
}
// 权限分配
$('#selectPower').click(function(){
    // 显示模态框
    $("#modal-demo").modal("show");
    $.ajax({
        async:false,
        cache:false,
        type: 'POST',
        data:{},
        dataType : "json",
        url: 'power',//请求的action路径
        success:function(res){ //请求成功后处理函数
            // console.log(res);
            treeNodes = res; //把后台封装好的简单Json格式赋给treeNodes
            $.fn.zTree.init($("#treeDemo"), setting, treeNodes);
            setCheck();
            $("#py").bind("change", setCheck);
            $("#sy").bind("change", setCheck);
            $("#pn").bind("change", setCheck);
            $("#sn").bind("change", setCheck);
        },
        error: function(res){
            console.log(res);
        },
    });
})
    // 提交表单
function addadmin_save_submit() {
    var admin_name = $('#admin_name').val();
    var admin_active = $('#admin_active option:selected').val();
    var admin_teacher = $('#admin_teacher option:selected').val();
    var password = $('#password').val();
    var password2 = $('#password2').val();
    if(admin_name==''){
        layer.msg('管理员名不能为空！',{icon:2,time:1300});
        return false;
    }
    if(password == '' || password2 == ''){
        layer.msg('密码不能为空！',{icon:2,time:1300});
        return false;
    }else if(password != password2){
        layer.msg('两次输入的密码不一致！',{icon:2,time:1300});
        return false;
    }
    // 发送ajax请求
    $.ajax({
        type: 'POST',
        url: 'addAdmin',
        dataType: 'json',
        data: {password: password, admin_name: admin_name,admin_active: admin_active,admin_teacher:admin_teacher},
        success: function(res){
            if(res.code == '0'){
                index = parent.layer.getFrameIndex(window.name);
                layer.msg(res.msg,{icon:1,time:1300});
                setTimeout('parent.layer.close(index);',1300);
            }
            if(res.code == '400'){
                layer.msg(res.msg,{icon:2,time:1300});
            }
        },
        error:function(res) {
            layer.msg('网络错误，请稍后重试!',{icon:2,time:1300});
        },
    });
    return false;
}
// 点击确定保存权限
$("#confirm").click(function(){
    if(aa){
        $.ajax({
            type: 'POST',
            data:{menuid:aa},
            dataType : "json",
            url: 'selectPower',
            success:function(res){ //请求成功后处理函数。
                if(res.code=='0'){
                    parent.layer.msg(res.msg, {icon: 1,time:1300});
                    $("#modal-demo").modal("hide");
                };
                if(res.code == '400'){
                    layer.msg(res.msg,{icon:2,time:1300});
                };
            },
            error:function(res) {
                console.log(res);
            }
        });
    }else{
        layer.msg('您尚未选择任何权限！',{icon:2,time:1300});
        return false;
    }
})