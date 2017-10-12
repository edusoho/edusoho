webpackJsonp(["app/js/course-manage/header/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	exports.publishCourse = undefined;
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var publishCourse = exports.publishCourse = function publishCourse() {
	  $('body').on('click', '.course-publish-btn', function (evt) {
	    if (!confirm(Translator.trans(Translator.trans('course.manage.publish_hint')))) {
	      return;
	    }
	    $.post($(evt.target).data('url'), function (data) {
	      if (data.success) {
	        (0, _notify2["default"])('success', Translator.trans('course.manage.publish_success_hint'));
	        location.reload();
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('course.manage.publish_fail_hint') + ':' + data.message, { delay: 5000 });
	      }
	    });
	  });
	};
	
	publishCourse();

/***/ })
]);
//# sourceMappingURL=index.js.map