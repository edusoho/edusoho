define(function(require, exports, module) {
	require('echarts-debug');
	var EchartsConfig = require('./echartsConfig');
	exports.run = function() {

		var myChart = echarts.init(document.getElementById('echats-pie'));
		var config = new EchartsConfig();
		myChart.setOption(config.option());

		$(".modal").off('click.modal-pagination');
		$(".modal").on('click', '.pagination a', function(e) {
			e.preventDefault();
			var urls = $(this).attr('href').split('?');
			url = [$("#pageinator-url").val(), urls[1]].join('?');
			$.get(url, function(html) {
				$(".referer-log-list").html(html);
			})
		});
	}
});