webpackJsonp(["app/js/classroom-manage/course-manage/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	var _sortable = __webpack_require__("8f840897d9471c8c1fbd");
	
	var _sortable2 = _interopRequireDefault(_sortable);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$(".course-list-group").on('click', '.close', function () {
	  if (confirm(Translator.trans('classroom.manage.delete_course_hint'))) {
	    $.post($(this).data('url'), function (resp) {
	      if (resp.success) {
	        (0, _notify2["default"])('success', Translator.trans('classroom.manage.delete_course_success_hint'));
	        window.location.reload();
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('classroom.manage.delete_course_fail_hint') + ':' + resp.message);
	      }
	    });
	  }
	});
	
	(0, _sortable2["default"])({
	  element: '#course-list-group',
	  itemSelector: "li",
	  ajax: false
	}, function (data) {
	  $('#courses-form').submit();
	});

/***/ })
]);
//# sourceMappingURL=index.js.map