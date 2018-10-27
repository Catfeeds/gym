    // 设置时间选择器的时间范围
    function selecttime(flag){
        var myDate = new Date();    
        var year = myDate.getFullYear();   //获取完整的年份(4位,1970-????)
        var month = myDate.getMonth()+1;   //获取当前月份(1-12)
        var day = myDate.getDate();        //获取当前日(1-31)
        //获取完整年月日
        var newDay = year + "-" + month + "-" + day;
        var minDate = year + "-" + (month-3) + "-" + day;
        if(flag==1){
            var endTime = $("#countTimeend").val();
            if(!endTime){
                WdatePicker({dateFmt:'yyyy-MM-dd', maxDate: newDay, minDate: minDate})
            }else{
                WdatePicker({dateFmt:'yyyy-MM-dd', maxDate: endTime, minDate: minDate})
            }
        }else{
            var startTime = $("#countTimestart").val();
            if(startTime != "" || !startTime){
                WdatePicker({dateFmt:'yyyy-MM-dd', minDate: minDate, maxDate: newDay})
            }else{
                WdatePicker({dateFmt:'yyyy-MM-dd', minDate: minDate, maxDate: newDay})
            }
        }
    }
// 下拉框更改
$('#time').change(function(){
        var select = $(this).val();
        if(select != 0){
            $('.datepicker').hide();
            getChartData(select);
        }else {
            $('.datepicker').show(); 
        }
    })   
    // 点击搜索
    $('#search').click(function(){
        if ($('#countTimestart').val() == "" || $('#countTimeend').val() == "") {
            layer.msg('请选择时间！',{icon:2,time:1000});
            return;
        }else{
            // 当前选择的时间
            var countTimestart =  $('#countTimestart').val();
            var countTimeend =  $('#countTimeend').val();
            // 发送ajax请求数据
            $.ajax({
                data:{ startTime: countTimestart,endTime:countTimeend},
                datatype: "POST",
                url: "getSearchData2",
                success:function(res){
                    // console.log(res);
                    if(res=='300'){
                        layer.msg('请至少选择一个月以上时间',{icon:2,time:1300});
                    }
                    var height =  500;
                    $('#container').css('height', height + 'px');
                    showLineChart(res.xData, res.seriesData, res.title);    
                },
                error: function(){
                    layer.msg('网络错误，请稍后重试!',{icon:2,time:1300});
                }
            });    
        }
    });
    // 初始化图表数据
    // getChartData('7');
    // AJAX获取图表数据
    function getChartData(select){
        $.ajax({
            data:{ select: select },
            datatype: "POST",
            url: "getIncomeData",
            success:function(res){
                // console.log(res);
                // 统计
                if(select == '7'){
                    var height =  res.seriesData[0].data.length * 50;
                    $('#container').css('height', height + 'px');
                    showLineChart(res.xData, res.seriesData, res.title); 
                }else {
                    var height =  res.seriesData[0].data.length * 30;
                    $('#container').css('height', height + 'px');
                    showLineChart(res.xData, res.seriesData, res.title);    
                }
            },
            error: function(){
                layer.msg('网络错误，请稍后重试!',{icon:2,time:1300});
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
                    text: '金额',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                // valueSuffix: ' 次'
                formatter: function() {  
                    return this.x+'<br/><br/>'+'<b>'+this.series.name+ ' 金额' +' '+this.y+'元'+'</b>';
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
                y: 20,
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