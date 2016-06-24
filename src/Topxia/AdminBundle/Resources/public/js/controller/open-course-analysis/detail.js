define(function(require, exports, module) {
	require('echarts-debug');

	exports.run = function() {

		function EchartsConfig() {
		}
		EchartsConfig.prototype = {
			legendData: function() {
				return eval("(" + $("#dataName").val() + ")");
			},
			seriesData: function() {
				var datas = eval("(" + $("#data").val() + ")");
				var optionDatas = [];
				datas.forEach(function(element, index, array) {
					optionDatas.push({
						name: element.refererHost,
						value: element.count
					})
				}, optionDatas);
				return optionDatas;
			},
			option: function() {
				return  {
					tooltip: {
						trigger: 'item',
						formatter: "{a} <br/>{b}: {c} ({d}%)"
					},
					legend: {
						orient: 'vertical',
						x: 'left',
						data: this.legendData()
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
						data: this.seriesData()
					}]
				};
			}
		}

		var myChart = echarts.init(document.getElementById('echats-pie'));
		var config = new EchartsConfig();
		myChart.setOption(config.option());
	}
});