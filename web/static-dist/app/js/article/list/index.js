webpackJsonp(["app/js/article/list/index"],[
/* 0 */
/***/ (function(module, exports) {

	import Swiper from 'swiper';
	
	if ($(".aricle-carousel .swiper-slide").length > 1) {
	    var swiper = new Swiper('.aricle-carousel .swiper-container', {
	        pagination: '.swiper-pager',
	        calculateHeight: true,
	        paginationClickable: true,
	        autoplay: 5000,
	        autoplayDisableOnInteraction: false,
	        loop: true,
	        onInit: function onInit(swiper) {
	            $(".swiper-slide").removeClass('swiper-hidden');
	        }
	    });
	}

/***/ })
]);