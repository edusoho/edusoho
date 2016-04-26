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

        getPageList('../../open/course/page/list');


        $('.open-course-list').on('click','.section-more-btn a',function(){
        	var url = $(this).attr('data-url');
	      	getPageList(url);
        })

	    function getPageList(url){
	    	$.post(url,function(response){
	    		$(".section-more-btn").remove();
		        $('.open-course-list').append(response);
		        Lazyload.init();
	    	})
	    }
    };

});