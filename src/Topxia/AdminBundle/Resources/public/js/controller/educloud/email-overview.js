define(function(require, exports, module) {
  require('echarts');

	exports.run = function() {

        var emailSendChart = echarts.init(document.getElementById('emailSendChart'));
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
        emailSendChart.setOption(option);
	}
	
});