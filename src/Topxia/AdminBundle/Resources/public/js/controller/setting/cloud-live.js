define(function(require, exports, module) {
    require('echarts');

    exports.run = function() {
        var liveTopChart = echarts.init(document.getElementById('liveTopChart'));
        var chartData = app.arguments.chartData;

         var liveoption = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:[Translator.trans('site.time')]
            },
            xAxis: {
                data: chartData.date
            },
            yAxis: {
                minInterval: 1
            },
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