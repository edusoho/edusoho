webpackJsonp(["app/js/activity/download/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	var _activityEmitter = __webpack_require__("da32dea28c2b82c7aab1");
	
	var _activityEmitter2 = _interopRequireDefault(_activityEmitter);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var emitter = new _activityEmitter2["default"]();
	
	$(".download-activity-list").on('click', 'a', function () {
	  $(this).attr('href', $(this).data('url'));
	  emitter.emit('finish', { fileId: $(this).data('fileId') });
	});
	$('#download-activity').perfectScrollbar();
	$('#download-activity').perfectScrollbar('update');

/***/ })
]);
//# sourceMappingURL=index.js.map