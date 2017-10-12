webpackJsonp(["app/js/settings/reset-pay-password/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('#settings-pay-password-form').validate({
	  currentDom: '#password-save-btn',
	  ajax: true,
	  rules: {
	    'oldPayPassword': {
	      required: true,
	      minlength: 5,
	      maxlength: 20
	    },
	    'newPayPassword': {
	      required: true,
	      minlength: 5,
	      maxlength: 20
	    },
	    'confirmPayPassword': {
	      required: true,
	      equalTo: '#form_newPayPassword'
	    }
	  },
	  submitSuccess: function submitSuccess(data) {
	    (0, _notify2["default"])('success', Translator.trans(data.message));
	
	    $('.modal').modal('hide');
	    window.location.reload();
	  },
	  submitError: function submitError(data) {
	    (0, _notify2["default"])('danger', Translator.trans(data.responseJSON.message));
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map