webpackJsonp(["app/js/mobile/index"],[
/* 0 */
/***/ (function(module, exports) {

	"use strict";
	
	$(".js-mobile-item").waypoint(function () {
	    $(this).addClass('active');
	}, { offset: 500 });
	
	$(".es-mobile .btn-mobile").click(function () {
	    $('html,body').animate({
	        scrollTop: $($(this).attr('data-url')).offset().top + 50
	    }, 300);
	});

/***/ })
]);
//# sourceMappingURL=index.js.map