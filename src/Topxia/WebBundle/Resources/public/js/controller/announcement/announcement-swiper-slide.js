define(function(require, exports, module) {
    var Swiper=require('swiper');
	var Cookie = require('cookie');

	exports.run = function() {
		$(".announcements .close").click(function(){
    		Cookie.set("close_" + $(this).data("targetType") + "_announcements_alert",'true',{path: '/'});
    	});
	}
});