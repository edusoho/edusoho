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
        $('#live, #free').on('click', function(event) {
        	$('input:checkbox').attr('checked',false);
        	$(this).attr('checked',true);

        	window.location.href = $(this).val();
        });
    };

});