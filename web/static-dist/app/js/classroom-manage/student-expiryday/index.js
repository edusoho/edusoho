webpackJsonp(["app/js/classroom-manage/student-expiryday/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#expiryday-set-form');
	var validator = $form.validate({
	  rules: {
	    deadline: {
	      required: true,
	      date: true
	    }
	  },
	  messages: {
	    deadline: {
	      required: '请输入有效期'
	    }
	  }
	});
	
	$('#student-save').click(function (event) {
	  if (validator.form()) {
	    $(event.currentTarget).button('loadding');
	    $.post($form.attr('action'), $form.serialize(), function (response) {
	      if (response == true) {
	        (0, _notify2["default"])('success', Translator.trans('classroom_manage.student_expiryday_set_success_hint'));
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('classroom_manage.student_expiryday_set_failed_hint'));
	      }
	      window.location.reload();
	    });
	  }
	});
	
	$("#student_deadline").datetimepicker({
	  language: document.documentElement.lang,
	  autoclose: true,
	  format: 'yyyy-mm-dd',
	  minView: 'month'
	});
	
	$("#student_deadline").datetimepicker('setStartDate', new Date());

/***/ })
]);
//# sourceMappingURL=index.js.map