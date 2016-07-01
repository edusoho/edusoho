define(function(require, exports, module) {
	require('echarts-debug');
	var EchartsConfig = require('./pieChartsConfig');
	exports.run = function() {
		var myChart = echarts.init(document.getElementById('echats-pie'));
		var config = new EchartsConfig();
		myChart.setOption(config.option());
	}
});