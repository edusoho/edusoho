webpackJsonp(["app/js/announcement/announcement-swiper-slide/index"],[
/* 0 */
/***/ (function(module, exports) {

	import Swiper from 'swiper';
	import Cookies from 'js-cookie';
	
	if ($('.announcements .swiper-container .swiper-wrapper').length > 0 && $('.announcements .swiper-container .swiper-wrapper').children().length > 1) {
	  var noticeSwiper = new Swiper('.announcements .swiper-container', {
	    speed: 300,
	    loop: true,
	    mode: 'vertical',
	    autoplay: 5000,
	    calculateHeight: true
	  });
	}

/***/ })
]);