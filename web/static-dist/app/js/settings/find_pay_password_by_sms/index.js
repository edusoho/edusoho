webpackJsonp(["app/js/settings/find_pay_password_by_sms/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _smsSender = __webpack_require__("0282bb17fb83bfbfed9b");
	
	var _smsSender2 = _interopRequireDefault(_smsSender);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var smsSend = '.js-sms-send';
	var $smsCode = $(smsSend);
	var $form = $('#settings-find-pay-password-form');
	var validator = $form.validate({
	  rules: {
	    sms_code: {
	      required: true,
	      unsigned_integer: true,
	      smsCode: true
	    }
	  },
	  messages: {
	    sms_code: {
	      required: Translator.trans('site.captcha_code.required')
	    }
	  }
	});
	
	$('#submit-btn').click(function () {
	  if (validator.form()) {
	    $form.submit();
	  }
	});
	
	$smsCode.on('click', function () {
	  new _smsSender2["default"]({
	    element: smsSend,
	    url: $smsCode.data('smsUrl'),
	    smsType: $smsCode.data('smsType'),
	    preSmsSend: function preSmsSend() {
	      return true;
	    }
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map