define(function(require, exports, module) {
	require('echarts-debug');

	exports.run = function() {

		var myChart = echarts.init(document.getElementById('echats-pie'));

		option = {
			tooltip: {
				trigger: 'item',
				formatter: "{a} <br/>{b}: {c} ({d}%)"
			},
			legend: {
				orient: 'vertical',
				x: 'left',
				data: eval("(" + $("#dataName").val() + ")")
			},
			series: [{
				name: '访问来源',
				type: 'pie',
				radius: '55%',
				center: ['50%', '60%'],
				avoidLabelOverlap: false,
				label: {
					normal: {
						show: false,
					},
					emphasis: {
						show: false,
						textStyle: {
							fontSize: '30',
							fontWeight: 'bold'
						}
					}
				},
				labelLine: {
					normal: {
						show: false
					}
				},
				data: eval("(" + $("#data").val() + ")")
			}]
		};

		myChart.setOption(option);

	}
});