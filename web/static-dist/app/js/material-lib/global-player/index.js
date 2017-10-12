webpackJsonp(["app/js/material-lib/global-player/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $element = $('#global-player');
	new QiQiuYun.Player({
	  id: 'global-player',
	  resNo: $element.data('resNo'),
	  token: $element.data('token'),
	  user: {
	    id: $element.data('userId'),
	    name: $element.data('userName')
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map