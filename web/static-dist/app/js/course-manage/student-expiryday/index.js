webpackJsonp(["app/js/course-manage/student-expiryday/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $modal = $('#expiryday-set-form').parents('.modal');
	var $form = $('#expiryday-set-form');
	
	var validator = $form.validate({
	  rules: {
	    expiryDay: {
	      positive_integer: true
	    }
	  }
	});
	
	$('.js-save-expiryday-set-form').click(function () {
	  if (validator.form()) {
	    $.post($form.attr('action'), $form.serialize(), function () {
	      var user_name = $('#submit').data('user');
	      (0, _notify2["default"])('success', Translator.trans('course_manage.student_expiryday_extend_success_hint', { name: user_name }));
	      $modal.modal('hide');
	      window.location.reload();
	    }).error(function () {
	      var user_name = $('#submit').data('user');
	      (0, _notify2["default"])('danger', Translator.trans('course_manage.student_expiryday_extend_failed_hint', { name: user_name }));
	    });
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map