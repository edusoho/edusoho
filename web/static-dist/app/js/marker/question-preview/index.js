webpackJsonp(["app/js/marker/question-preview/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$('.js-show-resolve').on('click', function () {
	  var $this = $(this);
	  $('.js-topic-content').toggleClass('hidden');
	  $('.js-topic-resolve').toggleClass('hidden').is(":visible") ? $this.text('返回题目') : $this.text('查看解析');
	});

/***/ })
]);
//# sourceMappingURL=index.js.map