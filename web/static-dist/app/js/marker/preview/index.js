webpackJsonp(["app/js/marker/preview/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("db9fa60e685f75e2d7f6");

/***/ }),

/***/ "db9fa60e685f75e2d7f6":
/***/ (function(module, exports) {

	'use strict';
	
	var videoHtml = $('#task-dashboard');
	var playerUrl = videoHtml.data("media-player");
	var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
	$("#task-video-content").html(html);

/***/ })

});
//# sourceMappingURL=index.js.map