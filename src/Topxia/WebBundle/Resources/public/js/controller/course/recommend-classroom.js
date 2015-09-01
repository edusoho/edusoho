define(function(require, exports, module) {
	var Swiper = require('swiper');
	exports.run = function() {
		var swiper = new Swiper('#panel-class .swiper-container', {

            paginationClickable: true,
            // autoplay: 5000,
            autoplayDisableOnInteraction: false,
            loop: true,
            calculateHeight: true,
            roundLengths: true,
            onInit: function(swiper) {
               $(".swiper-slide").removeClass('swiper-hidden'); 
            }
        });

        $('.arrow-prev').on('click', function(e){
            e.preventDefault();
            swiper.swipePrev();
        })
        $('.arrow-next').on('click', function(e){
            e.preventDefault();
            swiper.swipeNext();
        })
	}
});