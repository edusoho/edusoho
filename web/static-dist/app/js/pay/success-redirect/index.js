webpackJsonp(["app/js/pay/success-redirect/index"],[
/* 0 */
/***/ (function(module, exports) {

	"use strict";
	
	var item = $(".js-turn");
	countDown(3);
	function countDown(num) {
	  item.find(".js-countdown").text(num);
	  if (--num > 0) {
	    setTimeout(function () {
	      countDown(num);
	    }, 1000);
	  } else {
	    window.location.href = item.attr("data-url");
	  }
	}

/***/ })
]);
//# sourceMappingURL=index.js.map