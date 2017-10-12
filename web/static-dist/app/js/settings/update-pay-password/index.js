webpackJsonp(["app/js/settings/update-pay-password/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#pay-password-reset-update-form');
	
	var validator = $form.validate({
	  rules: {
	    'form[currentUserLoginPassword]': {
	      required: true
	    },
	    'form[payPassword]': {
	      required: true,
	      minlength: 5,
	      maxlength: 20
	    },
	    'form[confirmPayPassword]': {
	      required: true,
	      equalTo: '#form_payPassword'
	    }
	  }
	});
	
	console.log(validator);
	
	$('#payPassword-save-btn').on('click', function (event) {
	  var $this = $(event.currentTarget);
	  if (validator.form()) {
	    $this.button('loading');
	    $form.submit();
	  }
	});
	
	var messageDanger = $('.alert-danger').text();
	var messageSuccess = $('.alert-success').text();
	
	if (messageDanger) {
	  (0, _notify2["default"])('danger', Translator.trans(messageDanger));
	}
	
	if (messageSuccess) {
	  (0, _notify2["default"])('success', Translator.trans(messageSuccess));
	}

/***/ })
]);
//# sourceMappingURL=index.js.map