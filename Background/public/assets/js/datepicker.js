// 日期规则
function selecttime(flag){
    var myDate = new Date();    
    var year = myDate.getFullYear();   //获取完整的年份(4位,1970-????)
    var month = myDate.getMonth()+1;   //获取当前月份(1-12)
    var day = myDate.getDate();        //获取当前日(1-31)
    //获取完整年月日
    var newDay = year + "-" + month + "-" + day;
    var minDate = year + "-" + (month-1) + "-" + day;    
    var maxDate = year + "-" + (month+1) + "-" + day; 
    var startTime = $("#countTimestart").val();
    var endTime = $("#countTimeend").val();   
    if(flag==1){
        if(endTime != ""){
            WdatePicker({dateFmt:'yyyy-MM-dd', maxDate: newDay, minDate: minDate})
        }else{
            WdatePicker({dateFmt:'yyyy-MM-dd', maxDate: newDay, minDate: minDate})
        }
    }else{
        if(startTime != "" || !startTime){
            WdatePicker({dateFmt:'yyyy-MM-dd', minDate: startTime, maxDate: maxDate})
        }else{
            WdatePicker({dateFmt:'yyyy-MM-dd', minDate: startTime, maxDate: maxDate})
        }
    }
}
// 日期选择
function selecttime1(flag){   
    if(flag==1){
        var endTime = $("#countTimeend1").val();
        if(endTime != ""){
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }else{
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }
    }else{
        var startTime = $("#countTimestart1").val();
        if(startTime != "" || !startTime){
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }else{
            WdatePicker({dateFmt:'HH:mm', minDate:'00:00',maxDate:'24:00'})
        }
    }
}
// 日期选择
function selecttime2(flag){
    var myDate = new Date();    
    var year = myDate.getFullYear();   //获取完整的年份(4位,1970-????)
    var month = myDate.getMonth()+1;   //获取当前月份(1-12)
    var day = myDate.getDate();        //获取当前日(1-31)
    //获取完整年月日
    var newDay = year + "-" + month + "-" + day;
    var minDate = year + "-" + (month-1) + "-" + day;
    var maxDate = year + "-" + (month+1) + "-" + day;
    var startTime = $("#countTimestart2").val();
    var endTime = $("#countTimeend2").val();       
    if(flag==1){
        if(endTime != ""){
            WdatePicker({dateFmt:'yyyy-MM-dd HH:mm', maxDate: newDay, minDate: minDate})
        }else{
            WdatePicker({dateFmt:'yyyy-MM-dd HH:mm', maxDate: newDay, minDate: minDate})
        }
    }else{
        if(startTime != "" || !startTime){
            WdatePicker({dateFmt:'yyyy-MM-dd HH:mm', minDate: startTime, maxDate: maxDate})
        }else{
            WdatePicker({dateFmt:'yyyy-MM-dd HH:mm', minDate: startTime, maxDate: maxDate})
        }
    }
}