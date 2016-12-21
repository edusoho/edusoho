define(function(require, exports, module) {
    require('echarts');
    exports.run = function() {

        //改版图表
        var smsSendChart = echarts.init(document.getElementById('smsSendChart'));
        var chartData = app.arguments.chartData;
        var option = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:['时间']
            },
            xAxis: {
                data: chartData.date
            },
            yAxis: {
                minInterval: 1
            },
            series: [{
                name: '发送量(条)',
                type: 'bar',
                data: chartData.count
            }],
            color:['#428BCA'],
            grid:{
                show:true,
                borderColor:'#fff',
                backgroundColor:'#fff'
            }
        };
        smsSendChart.setOption(option);            
    }

});