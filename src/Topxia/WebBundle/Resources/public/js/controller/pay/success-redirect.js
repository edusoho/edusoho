define(function(require, exports, module) {

	exports.run = function() {
		var item = $(".js-turn");
		countDown(3);
		function countDown(num) {
			item.find(".js-countdown").text(num);
			if (--num > 0) {
				setTimeout(function(){countDown(num);},1000);
			}
			else {
				window.location.href = item.attr("data-url");
			}
		}
	};
});