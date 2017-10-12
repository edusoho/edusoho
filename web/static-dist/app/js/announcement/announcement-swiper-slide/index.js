webpackJsonp(["app/js/announcement/announcement-swiper-slide/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _swiper = __webpack_require__("370d3340744bf261df0e");
	
	var _swiper2 = _interopRequireDefault(_swiper);
	
	var _jsCookie = __webpack_require__("fe53252afd7b6c35cb73");
	
	var _jsCookie2 = _interopRequireDefault(_jsCookie);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	if ($('.announcements .swiper-container .swiper-wrapper').length > 0 && $('.announcements .swiper-container .swiper-wrapper').children().length > 1) {
	  var noticeSwiper = new _swiper2["default"]('.announcements .swiper-container', {
	    speed: 300,
	    loop: true,
	    mode: 'vertical',
	    autoplay: 5000,
	    calculateHeight: true
	  });
	}

/***/ })
]);
//# sourceMappingURL=index.js.map