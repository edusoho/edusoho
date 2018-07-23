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
                data:[Translator.trans('site.time')]
            },
            xAxis: {
                data: chartData.date
            },
            yAxis: {
                minInterval: 1
            },
            series: [{
                name: Translator.trans('admin.edu_cloud.sms_send_num'),
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