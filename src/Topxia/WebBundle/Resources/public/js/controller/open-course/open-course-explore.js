define(function(require, exports, module) {

    var Lazyload = require('echo.js');
    var Swiper = require('swiper');

    exports.run = function() {
    	
    	if ($(".es-live-poster .swiper-slide").length > 1) {
            var swiper = new Swiper('.es-live-poster.swiper-container', {
                pagination: '.swiper-pager',
                paginationClickable: true,
                autoplay: 5000,
                autoplayDisableOnInteraction: false,
                loop: true,
                calculateHeight: true,
                roundLengths: true,
                onInit: function(swiper) {
                   $(".swiper-slide").removeClass('swiper-hidden'); 
                }
            });
        }

        Lazyload.init();

        $(".section-more-btn a").on('click', function(){
      		var url = $(this).attr('data-url');
	      	$.ajax({
		        url: url,
		        dataType: 'html',
		        success: function(html) {
		          	var html = $('.open-course-list .course-block,.open-course-list .section-more-btn', $(html)).fadeIn('slow');
			        $(".section-more-btn").remove();
			        $('.open-course-list').append(html);
		        }
	      	});
	    });
    };

});