// 选择框事件
$('#subject_id').change(function(){
    getSubjectCourse();
});
$('#course_id').change(function(){
    getCourseClass();
});
// 选择框事件
$('#class_id').change(function(){
    getClassUser();
});
// 调用方法
getSubjectCourse();
// 请求科目对应课程
function getSubjectCourse(){
    $.ajax({
        type: 'POST',
        url: '../Msg/getSubjectCourse',
        dataType: 'json',
        data: {subject_id:$('#subject_id').val()},
        success: function (res) {
            if (res.code == 0) {
                $('#course_id option').remove();
                for (var i = 0; i < res.data.length; i++) {
                    var option = $('<option value="'+res.data[i].course_id+'">'+res.data[i].course_name+'</option>');
                    $('#course_id').append(option);
                }
                // 请求课程对应班级           
                getCourseClass();
                // 请求课程对应打卡次数与结束时间
                $('#class_id').val('');   
                $('#user').val('');
            }
            if (res.code == 400) {
                $('#course_id option').remove();
                layer.alert(res.msg, {icon: 2});
            }
         },
        error: function () {
            layer.msg('请求错误，请稍后重试！', { icon: 2, time: 1000 });
        },
    });        
}
// 请求课程对应班级
function getCourseClass(){
    $.ajax({
        type: 'POST',
        url: '../Msg/courseClass',
        dataType: 'json',
        data: {id:$('#course_id').val()},
        success: function (data) {
            if (data.code == "0") {
                $('#class_id option').remove();
                for (var i = 0; i < data.data.length; i++) {
                    var option = $('<option value="'+data.data[i].class_id+'">'+data.data[i].class_name+'</option>');
                    $('#class_id').append(option);
                }
            }
            if (data.code == '400') {
                $('#class_id option').remove();
                layer.msg(data.msg, { icon: 2, time: 1000 });
            }
            getClassUser();
        },
        error: function () {
            layer.msg('请求错误，请稍后重试！', { icon: 2, time: 1000 });
        },
    });        
}
// 请求班级对应学生
function getClassUser(){
    $.ajax({
        type: 'POST',
        url: '../Msg/classUser',
        dataType: 'json',
        data: {id:$('#class_id').val()},
        success: function (data) {
            if (data.code == "0") {
                $('#user option').remove();
                for (var i = 0; i < data.data.length; i++) {
                    var option = $('<option value="'+data.data[i].uid+'">'+data.data[i].username+'</option>');
                    $('#user').append(option);
                }
            }
            if (data.code == '400') {
                $('#user option').remove();
                layer.msg(data.msg, { icon: 2, time: 1000 });
            }
        },
        error: function () {
            layer.msg('请求错误，请稍后重试！', { icon: 2, time: 1000 });
        },
    });
}    
// 点击搜索
$("#search").click(function(){
    var uid = $("#user").val();
    console.log(uid);
    var classId = $("#class_id").val();
    if(classId == null){
        layer.msg('无班级信息!',{icon:2,time:1300});
        return false;
    }
    if(uid == null){
        layer.msg('无学生信息!',{icon:2,time:1300});
        return false;
    }
    // 初始化图表数据
    getChartData(uid,classId); 
})
// AJAX获取图表数据
function getChartData(uid,classId){
    
    $.ajax({
        data:{uid: uid,classId:classId},
        datatype: "POST",
        url: "userClockChart",
        success:function(res){
            console.log(res);
            if(res.xData == ''){
                layer.alert('暂无数据！',{icon: 2});
            }
            // 统计
            var height =  res.seriesData[0].data.length * 50;
            $('#container').css('height', height + 'px');
            showLineChart(res.xData, res.seriesData, res.title); 
        },
        error: function(){
            layer.msg('请求错误，请稍后重试!',{icon:2,time:1300});
        }
    });
}
// 折线图
function showLineChart(xData, seriesData, title){
    var chartOptions = {  
        chart: {
            type: 'line'
        },
        title: {
            text: title,
            x: -20 //center
        },
        // subtitle: {
        //     text: '数据来源: 本地'
        // },
        xAxis: {
            categories: xData,
            title: {
                text: '日期'
            },
            labels: {
                overflow: 'justify'
            }
        },
        yAxis: {
            min: 0,
            tickInterval: 1,
            title: {
                text: '打卡情况',
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        tooltip: {
            // valueSuffix: ' 次'
            formatter: function() {  
                return this.x+'<br/><br/>'+'<b>'+this.series.name +' '+'</b>';
            } 
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true,
                    allowOverlap: true  // 允许数据标签重叠
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 0,
            floating: true,
            borderWidth: 0,
            // backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: seriesData
    };
    // 图表初始化函数
    var chart = Highcharts.chart('container', chartOptions);        
}
// 设置默认参数
Highcharts.setOptions({
    lang: {
        printChart:['打印图表'],
        downloadJPEG:['导出JPEG图片'],
        downloadPDF:['导出PDF文件'],
        downloadPNG:['导出PNG图片'],
        downloadSVG:['导出SVG文件']
    }
});