webpackJsonp(["app/js/settings/password/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('#settings-password-form').validate({
	  currentDom: '#password-save-btn',
	  ajax: true,
	  rules: {
	    'currentPassword': {
	      required: true
	    },
	    'newPassword': {
	      required: true,
	      minlength: 5,
	      maxlength: 20,
	      visible_character: true
	    },
	    'confirmPassword': {
	      required: true,
	      equalTo: '#form_newPassword',
	      visible_character: true
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