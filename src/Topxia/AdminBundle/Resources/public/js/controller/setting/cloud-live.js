define(function(require, exports, module) {
    require('echarts-debug');

    exports.run = function() {
        var liveTopChart = echarts.init(document.getElementById('liveTopChart'));
        var chartData = app.arguments.chartData;

         var liveoption = {
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
            yAxis: {},
            series: [{
                name: chartData.unit,
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
        liveTopChart.setOption(liveoption);
    }
});