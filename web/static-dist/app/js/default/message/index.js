webpackJsonp(["app/js/default/message/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $message = $("#page-message-container");
	var gotoUrl = $message.data('goto');
	var duration = $message.data('duration');
	if (duration > 0 && gotoUrl) {
	  setTimeout(function () {
	    window.location.href = gotoUrl;
	  }, duration);
	}

/***/ })
]);
//# sourceMappingURL=index.js.map