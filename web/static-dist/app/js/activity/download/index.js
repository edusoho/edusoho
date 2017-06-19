webpackJsonp(["app/js/activity/download/index"],[
/* 0 */
/***/ (function(module, exports) {

	import ActivityEmitter from "../activity-emitter";
	
	var emitter = new ActivityEmitter();
	
	$(".download-activity-list").on('click', 'a', function () {
	  $(this).attr('href', $(this).data('url'));
	  emitter.emit('finish', { fileId: $(this).data('fileId') });
	});
	$('#download-activity').perfectScrollbar();
	$('#download-activity').perfectScrollbar('update');

/***/ })
]);