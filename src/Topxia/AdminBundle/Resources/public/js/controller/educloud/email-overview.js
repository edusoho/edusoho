define(function(require, exports, module) {
  require('echarts-debug');

	exports.run = function() {

        var emailSendChart = document.getElementById('emailSendChart');
        if (emailSendChart) {
            var emailSendChart = echarts.init(emailSendChart);
            var items = app.arguments.items;
            var option = {
                title: {
                    text: ''
                },
                tooltip: {},
                legend: {
                    data:['时间']
                },
                xAxis: {
                    data: items.date
                },
                yAxis: {},
                series: [{
                    name: '发送量(条)',
                    type: 'bar',
                    data: items.amount
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
	}
	
});