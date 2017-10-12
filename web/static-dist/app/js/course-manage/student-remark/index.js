webpackJsonp(["app/js/course-manage/student-remark/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $modal = $('#student-remark-form').parents('.modal');
	var $form = $('#student-remark-form');
	
	var validator = $form.validate({
	  rules: {
	    remark: {
	      required: false,
	      maxlength: 80
	    }
	  },
	  messages: {
	    remark: {
	      maxlength: Translator.trans('course_manage.student_remark_validate_error_hint')
	    }
	  }
	});
	
	$('.js-student-remark-save-btn').click(function (event) {
	  if (validator.form()) {
	    $(event.currentTarget).button('loadding');
	    $.post($form.attr('action'), $form.serialize(), function (resp) {
	      $modal.modal('hide');
	      var user_name = $form.data('user');
	      (0, _notify2["default"])('success', Translator.trans('course_manage.student_remark_success_hint', { username: user_name }), { delay: 1000, onClose: function onClose() {
	          window.location.reload();
	        } });
	    }).error(function () {
	      var user_name = $form.data('user');
	      (0, _notify2["default"])('danger', Translator.trans('course_manage.student_remark_failed_hint', { username: user_name }));
	    });
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map