webpackJsonp(["app/js/auth/register/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _register = __webpack_require__("9ffde76f31e1d8ca79f0");
	
	var _register2 = _interopRequireDefault(_register);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	new _register2.default();

/***/ }),

/***/ "9ffde76f31e1d8ca79f0":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Register = function () {
	  function Register() {
	    _classCallCheck(this, Register);
	
	    this.initDate();
	    this.initValidator();
	    this.inEventMobile();
	    this.initCaptchaCode();
	    this.initRegisterTypeRule();
	    this.initInviteCodeRule();
	    this.intiUserTermsRule();
	  }
	
	  _createClass(Register, [{
	    key: 'initValidator',
	    value: function initValidator() {
	      var validator = $('#register-form').validate({
	        rules: {
	          nickname: {
	            required: true,
	            byte_minlength: 4,
	            byte_maxlength: 18,
	            nickname: true,
	            chinese_alphanumeric: true,
	            es_remote: {
	              type: 'get'
	            }
	          },
	          password: {
	            minlength: 5,
	            maxlength: 20
	          }
	        }
	      });
	
	      $.validator.addMethod("email_or_mobile_check", function (value, element, params) {
	        var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	        var reg_mobile = /^1\d{10}$/;
	        var result = false;
	        var isEmail = reg_email.test(value);
	        var isMobile = reg_mobile.test(value);
	        if (isMobile) {
	          $(".email_mobile_msg").removeClass('hidden');
	          $('.js-captcha').addClass('hidden');
	        } else {
	          $(".email_mobile_msg").addClass('hidden');
	          $('.js-captcha').removeClass('hidden');
	        }
	        if (isEmail || isMobile) {
	          result = true;
	        }
	        $.validator.messages.email_or_mobile_check = Translator.trans('请输入正确的手机／邮箱');
	        return this.optional(element) || result;
	      }, Translator.trans('不允许以1开头的11位纯数字'));
	    }
	  }, {
	    key: 'inEventMobile',
	    value: function inEventMobile() {
	      var _this = this;
	
	      $("#register_emailOrMobile").blur(function () {
	        var emailOrMobile = $("#register_emailOrMobile").val();
	        _this.emSmsCodeValidate(emailOrMobile);
	      });
	
	      $("#register_mobile").blur(function () {
	        var mobile = $("#register_mobile").val();
	        _this.emSmsCodeValidate(mobile);
	      });
	    }
	  }, {
	    key: 'initDate',
	    value: function initDate() {
	      $(".date").datetimepicker({
	        autoclose: true,
	        format: 'yyyy-mm-dd',
	        minView: 'month'
	      });
	    }
	  }, {
	    key: 'initCaptchaCode',
	    value: function initCaptchaCode() {
	      var $getCodeNum = $('#getcode_num');
	      if ($getCodeNum.length > 0) {
	        $getCodeNum.click(function () {
	          $(this).attr("src", $getCodeNum.data("url") + "?" + Math.random());
	        });
	        this.initCaptchaCodeRule();
	      }
	    }
	  }, {
	    key: 'initRegisterTypeRule',
	    value: function initRegisterTypeRule() {
	      var $email = $('input[name="email"]');
	      if ($email.length > 0) {
	        $email.rules('add', {
	          required: true,
	          email: true,
	          es_remote: {
	            type: 'get'
	          },
	          messages: {
	            required: Translator.trans('请输入邮箱')
	          }
	        });
	      }
	
	      var $emailOrMobile = $('input[name="emailOrMobile"]');
	      if ($emailOrMobile.length > 0) {
	        $emailOrMobile.rules('add', {
	          required: true,
	          email_or_mobile_check: true,
	          es_remote: {
	            type: 'get',
	            callback: function callback(bool) {
	              if (bool) {
	                $('.js-sms-send').removeClass('disabled');
	              } else {
	                $('.js-sms-send').addClass('disabled');
	              }
	            }
	          },
	          messages: {
	            required: Translator.trans('请输入手机/邮箱')
	          }
	        });
	      }
	
	      var $verifiedMobile = $('input[name="verifiedMobile"]');
	      if ($verifiedMobile.length > 0) {
	        $('.email_mobile_msg').removeClass('hidden');
	        $verifiedMobile.rules('add', {
	          required: true,
	          phone: true,
	          es_remote: {
	            type: 'get',
	            callback: function callback(bool) {
	              if (bool) {
	                $('.js-sms-send').removeClass('disabled');
	              } else {
	                $('.js-sms-send').addClass('disabled');
	              }
	            }
	          },
	          messages: {
	            required: Translator.trans('请输入手机')
	          }
	        });
	      }
	    }
	  }, {
	    key: 'initInviteCodeRule',
	    value: function initInviteCodeRule() {
	      var $invitecode = $('.invitecode');
	      if ($invitecode.length > 0) {
	        $invitecode.rules('add', {
	          required: false,
	          reg_inviteCode: true,
	          es_remote: {
	            type: 'get'
	          }
	        });
	      }
	    }
	  }, {
	    key: 'intiUserTermsRule',
	    value: function intiUserTermsRule() {
	      if ($('#user_terms').length) {
	        $('#user_terms').rules('add', {
	          required: true,
	          messages: {
	            required: Translator.trans('勾选同意此服务协议，才能继续注册')
	          }
	        });
	      }
	    }
	  }, {
	    key: 'initCaptchaCodeRule',
	    value: function initCaptchaCodeRule() {
	      $('[name="captcha_code"]').rules('add', {
	        required: true,
	        alphanumeric: true,
	        es_remote: {
	          type: 'get',
	          callback: function callback(bool) {
	            if (!bool) {
	              $('#getcode_num').attr("src", $('#getcode_num').data("url") + "?" + Math.random());
	            }
	          }
	        }
	      });
	    }
	  }, {
	    key: 'initSmsCodeRule',
	    value: function initSmsCodeRule() {
	      $('[name="sms_code"]').rules('add', {
	        required: true,
	        rangelength: [6, 6],
	        es_remote: {
	          type: 'get'
	        },
	        messages: {
	          rangelength: Translator.trans('请输入6位验证码')
	        }
	      });
	    }
	  }, {
	    key: 'emSmsCodeValidate',
	    value: function emSmsCodeValidate(mobile) {
	      var reg_mobile = /^1\d{10}$/;
	      var isMobile = reg_mobile.test(mobile);
	      if (isMobile) {
	        this.initSmsCodeRule();
	        $('[name="captcha_code"]').rules('remove');
	      } else {
	        this.initCaptchaCodeRule();
	        $('[name="sms_code"]').rules('remove');
	      }
	    }
	  }]);
	
	  return Register;
	}();
	
	export default Register;

/***/ })

});