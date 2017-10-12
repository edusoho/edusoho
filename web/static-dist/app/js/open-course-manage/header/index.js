webpackJsonp(["app/js/open-course-manage/header/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('.course-publish-btn').click(function () {
	  if (!confirm(Translator.trans('open_course.publish_hint'))) {
	    return;
	  }
	  $.post($(this).data('url'), function (response) {
	    if (!response['result']) {
	      (0, _notify2["default"])('danger', response['message']);
	    } else {
	      window.location.reload();
	    }
	  });
	});
	
	$('.js-exit-course').on('click', function () {
	  var self = $(this);
	  $.post($(this).data('url'), function () {
	    window.location.href = self.data('go');
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map