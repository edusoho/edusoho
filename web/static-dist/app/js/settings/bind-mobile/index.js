webpackJsonp(["app/js/settings/bind-mobile/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _smsSender = __webpack_require__("0282bb17fb83bfbfed9b");
	
	var _smsSender2 = _interopRequireDefault(_smsSender);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#bind-mobile-form');
	var smsSend = '.js-sms-send';
	var $smsCode = $(smsSend);
	
	$form.validate({
	  currentDom: '#submit-btn',
	  ajax: true,
	  rules: {
	    password: {
	      required: true,
	      es_remote: {
	        type: 'post'
	      }
	    },
	    mobile: {
	      required: true,
	      phone: true,
	      es_remote: {
	        type: 'get',
	        callback: function callback(bool) {
	          if (bool) {
	            $smsCode.removeAttr('disabled');
	          } else {
	            $smsCode.attr('disabled', true);
	          }
	        }
	      }
	    },
	    sms_code: {
	      required: true,
	      unsigned_integer: true,
	      es_remote: {
	        type: 'get'
	      }
	    }
	  },
	  messages: {
	    sms_code: {
	      required: Translator.trans('site.captcha_code.required')
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
	
	$smsCode.on('click', function () {
	  new _smsSender2["default"]({
	    element: smsSend,
	    url: $smsCode.data('url'),
	    smsType: 'sms_bind'
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map