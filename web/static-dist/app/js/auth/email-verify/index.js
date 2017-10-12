webpackJsonp(["app/js/auth/email-verify/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$.post($('[name=verifyUrl]').val(), function (response) {
	  if (true == response) {
	    setTimeout(function () {
	      window.location.href = $("#jump-btn").attr('href');
	    }, 2000);
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map