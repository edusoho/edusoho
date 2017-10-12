webpackJsonp(["app/js/open-course-manage/recommend-course/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _sortable = __webpack_require__("8f840897d9471c8c1fbd");
	
	var _sortable2 = _interopRequireDefault(_sortable);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$(".course-list-group").on('click', '.close', function () {
	  var recommendId = $(this).data('recommendId');
	  var courseId = $(this).data('id');
	  $.post($(this).data('cancelUrl')).done(function () {
	
	    $('.item-' + courseId).remove();
	  });
	});
	
	(0, _sortable2["default"])({
	  element: '.course-list-group',
	  itemSelector: "li.course-item",
	  ajax: false
	});

/***/ })
]);
//# sourceMappingURL=index.js.map