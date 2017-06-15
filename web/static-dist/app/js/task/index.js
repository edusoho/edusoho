webpackJsonp(["app/js/task/index"],[
/* 0 */
/***/ (function(module, exports) {

	import TaskShow from './task';
	
	new TaskShow({
	  element: $('body'),
	  mode: $('body').find('#js-hidden-data [name="mode"]').val()
	});

/***/ })
]);