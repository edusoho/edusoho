webpackJsonp(["app/js/auth/captcha-modal/index"],{

/***/ "601020d4b7c53037d8f9":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _smsSender = __webpack_require__("0282bb17fb83bfbfed9b");
	
	var _smsSender2 = _interopRequireDefault(_smsSender);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CaptchaModal = function () {
	  function CaptchaModal($element, dataTo, smsType, captchaNum) {
	    _classCallCheck(this, CaptchaModal);
	
	    this.$element = $element;
	    this.dataTo = dataTo;
	    this.smsType = smsType;
	    this.captchaNum = captchaNum;
	    this.CaptchaValidator = null;
	    this.init();
	  }
	
	  _createClass(CaptchaModal, [{
	    key: 'init',
	    value: function init() {
	      var _this = this;
	
	      this.$element.on('click', '#getcode_num', function (event) {
	        return _this.changeCaptcha(event);
	      });
	      $('.js-captcha-btn').click(function (event) {
	        return _this.submitForm(event);
	      });
	      this.initValidator();
	    }
	  }, {
	    key: 'changeCaptcha',
	    value: function changeCaptcha(e) {
	      var $code = $(e.currentTarget);
	      $code.attr("src", $code.data("url") + "?" + Math.random());
	    }
	  }, {
	    key: 'submitForm',
	    value: function submitForm() {
	      if (this.CaptchaValidator.form()) {
	        this.$element.submit();
	      }
	    }
	  }, {
	    key: 'initValidator',
	    value: function initValidator() {
	      var _this2 = this;
	
	      this._captchaValidated = false;
	      this.CaptchaValidator = this.$element.validate({
	        onkeyup: false,
	        onfocusout: false,
	        rules: {
	          captcha_num: {
	            required: true,
	            alphanumeric: true
	          }
	        },
	        messages: {
	          captcha_num: {
	            required: Translator.trans('auth.mobile_captcha_required_error_hint')
	          }
	        },
	        submitHandler: function submitHandler(form) {
	          console.log('submitHandler');
	          $.get(_this2.$element.attr('action'), { value: $('#captcha_num_modal').val() }, function (response) {
	            if (response.success) {
	              _this2.$element.parents('.modal').modal('hide');
	              _this2._captchaValidated = true;
	              var smsSender = new _smsSender2["default"]({
	                element: '.js-sms-send',
	                url: $('.js-sms-send').data('smsUrl'),
	                smsType: _this2.smsType,
	                dataTo: _this2.dataTo,
	                captchaNum: _this2.captchaNum,
	                captcha: true,
	                captchaValidated: _this2._captchaValidated,
	                preSmsSend: function preSmsSend() {
	                  var couldSender = true;
	                  return couldSender;
	                }
	              });
	              $('.js-sms-send').off('click');
	            } else {
	              _this2._captchaValidated = false;
	              _this2.$element.find('#getcode_num').attr("src", $("#getcode_num").data("url") + "?" + Math.random());
	              _this2.$element.find('.help-block').html('<span class="color-danger">' + Translator.trans('auth.mobile_captcha_error_hint') + '</span>');
	              _this2.$element.find('.help-block').show();
	            }
	          }, 'json');
	        }
	      });
	      $('#captcha_num_modal').keydown(function (event) {
	        if (event.keyCode == 13) {
	          _this2.CaptchaValidator.form();
	        }
	      });
	    }
	  }]);
	
	  return CaptchaModal;
	}();
	
	exports["default"] = CaptchaModal;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _captchaMobileModal = __webpack_require__("601020d4b7c53037d8f9");
	
	var _captchaMobileModal2 = _interopRequireDefault(_captchaMobileModal);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var dataTo = '';
	var smsType = '';
	var captchaNum = 'captcha_num';
	if ($('input[name="set_bind_emailOrMobile"]').length > 0) {
	  dataTo = 'set_bind_emailOrMobile';
	  smsType = 'sms_registration';
	} else if ($('input[name="mobile"]').length > 0) {
	  dataTo = 'mobile';
	  if ($('#password-reset-by-mobile-form').length > 0) {
	    smsType = 'sms_forget_password';
	  } else if ($('#settings-find-pay-password-form').length > 0) {
	    smsType = 'sms_forget_pay_password';
	  } else {
	    smsType = 'sms_bind';
	  }
	} else {
	  dataTo = $('[name="verifiedMobile"]').val() == null ? 'emailOrMobile' : 'verifiedMobile';
	  smsType = 'sms_registration';
	}
	
	$('#captcha-form').find('#getcode_num').attr("src", $("#getcode_num").data("url") + "?" + Math.random());
	
	var captchaModal = new _captchaMobileModal2["default"]($('#captcha-form'), dataTo, smsType, captchaNum);
	
	console.log($('#captcha-form'));
	console.log(captchaModal);

/***/ })

});
//# sourceMappingURL=index.js.map