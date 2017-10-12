webpackJsonp(["app/js/settings/setup-password/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#setup-password-form');
	
	$form.validate({
	  ajax: true,
	  rules: {
	    currentDom: '#form-submit',
	    'form[newPassword]': {
	      required: true,
	      minlength: 5,
	      maxlength: 20
	    },
	    'form[confirmPassword]': {
	      required: true,
	      equalTo: '#form_newPassword'
	    }
	  },
	  submitSuccess: function submitSuccess(res) {
	    (0, _notify2["default"])('success', Translator.trans(res.message));
	    if ($form.data('targeType') == 'modal') {
	      $('#modal').load($form.data('goto')).modal('show');
	    } else {
	      window.location.href = res.data.targetPath;
	    }
	
	    return false;
	  },
	  submitError: function submitError(data) {
	    (0, _notify2["default"])('danger', Translator.trans(data.responseJSON.message));
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map