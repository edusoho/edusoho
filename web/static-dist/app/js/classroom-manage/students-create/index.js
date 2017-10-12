webpackJsonp(["app/js/classroom-manage/students-create/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $modal = $('#student-create-form').parents('.modal');
	var $form = $('#student-create-form');
	var $table = $('#course-student-list');
	var $btn = $("#student-create-form-submit");
	var validator = $form.validate({
	  onkeyup: false,
	  rules: {
	    queryfield: {
	      required: true,
	      remote: {
	        url: $('#student-nickname').data('url'),
	        type: 'get',
	        data: {
	          'value': function value() {
	            return $('#student-nickname').val();
	          }
	        }
	      }
	    },
	    remark: {
	      maxlength: 80
	    },
	    price: {
	      currency: true
	    }
	  },
	  messages: {
	    queryfield: {
	      remote: Translator.trans('classroom_manage.student_create_field_required_error_hint')
	    }
	  }
	});
	
	$btn.click(function () {
	  if (validator.form()) {
	    $btn.button('submiting').addClass('disabled');
	    $.post($form.attr('action'), $form.serialize(), function () {
	      $modal.modal('hide');
	      (0, _notify2["default"])('success', Translator.trans('classroom_manage.student_create_add_success_hint'));
	      window.location.reload();
	    }).error(function () {
	      (0, _notify2["default"])('danger', Translator.trans('classroom_manage.student_create_add_failed_hint'));
	      $btn.button('reset').removeClass('disabled');
	    });
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map