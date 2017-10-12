webpackJsonp(["app/js/article/list/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _swiper = __webpack_require__("370d3340744bf261df0e");
	
	var _swiper2 = _interopRequireDefault(_swiper);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	if ($(".aricle-carousel .swiper-slide").length > 1) {
	    var swiper = new _swiper2["default"]('.aricle-carousel .swiper-container', {
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
//# sourceMappingURL=index.js.map