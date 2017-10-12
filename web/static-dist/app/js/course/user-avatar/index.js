webpackJsonp(["app/js/course/user-avatar/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("d5e8fa5f17ac5fe79c78");
	
	$(".js-course-avatar").on('click', function () {
	    store.set('COURSE-GUEST-PAGE-URL', window.location.href);
	    this.href = $(this).data('url');
	});

/***/ })
]);
//# sourceMappingURL=index.js.map