define(function(require, exports, module) {


	function EchartsConfig(options) {
		var defaultOption = {
			name: '访问来源',
			formatter: "{a} <br/>{b}: {c} ({d}%)"
		}
		this.config = $.extend(defaultOption, options);
	}
	EchartsConfig.prototype = {
		legendData: function() {
			return JSON.parse($("#dataName").val());
		},
		seriesData: function() {
			var datas = JSON.parse($("#data").val());
			var optionDatas = [];
			datas.forEach(function(element, index, array) {
				optionDatas.push({
					name: element.refererName,
					value: element.count
				})
			}, optionDatas);
			return optionDatas;
		},
		option: function() {
			return {
				tooltip: {
					trigger: 'item',
					formatter: this.config.formatter
				},
				legend: {
					orient: 'vertical',
					x: 'left',
					data: this.legendData()
				},
				series: [{
					name: this.config.name,
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

	module.exports = EchartsConfig;
});