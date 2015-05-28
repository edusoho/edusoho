define(function(require, exports, module) {
    var Swiper=require('swiper');
	var Cookie = require('cookie');

	exports.run = function() {
		var noticeSwiper = new Swiper('.announcements .swiper-container', {
            speed: 300,
            loop: true,
            mode: 'vertical',
            autoplay: 5000
        });
		
		$(".announcements .close").click(function(){
    		Cookie.set("close_" + $(this).data("targetType") + "_announcements_alert",'true',{path: '/'});
    	});
	}
});