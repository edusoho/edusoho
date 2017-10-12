webpackJsonp(["app/js/user/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$('.follow-btn').on('click', function () {
	    var $this = $(this);
	    $.post($this.data('url'), function () {
	        $this.hide();
	        $this.next('.unfollow-btn').show();
	    });
	});
	
	$('.unfollow-btn').on('click', function () {
	    var $this = $(this);
	    $.post($this.data('url'), function () {
	        $this.hide();
	        $this.prev('.follow-btn').show();
	    });
	});
	
	$(".user-center-header").blurr({ height: 220 });
	
	echo.init();

/***/ })
]);
//# sourceMappingURL=index.js.map