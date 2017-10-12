webpackJsonp(["app/js/course-manage/live-replay/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('.js-generate-replay').on('click', function (event) {
	  var $this = $(event.currentTarget);
	  var url = $this.data('url');
	  if (!url) return;
	  Promise.resolve($.post(url)).then(function (success) {
	    (0, _notify2["default"])('success', Translator.trans('course.manage.live_replay_generate_success'));
	    window.location.reload();
	  })["catch"](function (response) {
	    var error = JSON.parse(response.responseText);
	    var code = error.code;
	    var message = error.error;
	    (0, _notify2["default"])('danger', '发生了异常，请稍后重试');
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map