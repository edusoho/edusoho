define(function(require, exports, module) {


	function EchartsConfig(options) {
		var defaultOption = {
			name: Translator.trans('admin.open_course_analysis.chart_title'),
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
				/*legend: {
					orient: 'vertical',
					x: 'left',
					data: this.legendData()
				},*/
				series: [{
					name: this.config.name,
					type: 'pie',
					radius: '55%',
					center: ['50%', '60%'],
					itemStyle: {
		                emphasis: {
		                    shadowBlur: 10,
		                    shadowOffsetX: 0,
		                    shadowColor: 'rgba(0, 0, 0, 0.5)'
		                }
		            },
					color:['#c23531','#2f4554', '#61a0a8', '#d48265', '#91c7ae','#749f83',  '#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3'],
					data: this.seriesData()
				}]
			};
		}
	}

	module.exports = EchartsConfig;
});