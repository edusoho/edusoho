webpackJsonp(["app/js/user/fill-info/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _userinfoFieldsCommon = __webpack_require__("b843b6d59bfac301cf77");
	
	var _userinfoFieldsCommon2 = _interopRequireDefault(_userinfoFieldsCommon);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _userinfoFieldsCommon2["default"]({
	  element: '#fill-userinfo-form'
	});

/***/ }),

/***/ "b843b6d59bfac301cf77":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _smsSender = __webpack_require__("0282bb17fb83bfbfed9b");
	
	var _smsSender2 = _interopRequireDefault(_smsSender);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var UserInfoFieldsItemValidate = function () {
	  function UserInfoFieldsItemValidate(options) {
	    _classCallCheck(this, UserInfoFieldsItemValidate);
	
	    this.validator = null;
	    this.$element = $(options.element);
	    this.setup();
	  }
	
	  _createClass(UserInfoFieldsItemValidate, [{
	    key: 'setup',
	    value: function setup() {
	      this.createValidator();
	      this.initComponents();
	      this.smsCodeValidate();
	      this.initEvent();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '#getcode_num', function (event) {
	        return _this.changeCaptcha(event);
	      });
	      this.$element.on('click', '.js-sms-send', function (event) {
	        return _this.sendSms(event);
	      });
	    }
	  }, {
	    key: 'initComponents',
	    value: function initComponents() {
	      $('.date').each(function () {
	        $(this).datetimepicker({
	          autoclose: true,
	          format: 'yyyy-mm-dd',
	          minView: 2,
	          language: document.documentElement.lang
	        });
	      });
	    }
	  }, {
	    key: 'createValidator',
	    value: function createValidator() {
	      this.validator = this.$element.validate({
	        currentDom: '#form-submit-btn',
	        rules: {
	          email: {
	            required: true,
	            email: true,
	            remote: {
	              url: $('#email').data('url'),
	              type: 'get',
	              data: {
	                'value': function value() {
	                  return $('#email').val();
	                }
	              }
	            }
	          },
	          mobile: {
	            required: true,
	            phone: true,
	            remote: {
	              url: $('#mobile').data('url'),
	              type: 'get',
	              data: {
	                'value': function value() {
	                  return $('#mobile').val();
	                }
	              }
	            }
	          },
	          truename: {
	            required: true,
	            chinese_alphanumeric: true,
	            minlength: 2,
	            maxlength: 36
	          },
	          qq: {
	            required: true,
	            qq: true
	          },
	          idcard: {
	            required: true,
	            idcardNumber: true
	          },
	          gender: {
	            required: true
	          },
	          company: {
	            required: true
	          },
	          job: {
	            required: true
	          },
	          weibo: {
	            required: true,
	            url: true
	          },
	          weixin: {
	            required: true
	          }
	        },
	        messages: {
	          gender: {
	            required: Translator.trans('site.choose_gender_hint')
	          },
	          mobile: {
	            phone: Translator.trans('validate.phone.message')
	          }
	        },
	        submitHandler: function submitHandler(form) {
	          if ($(form).valid()) {
	            $.post($(form).attr('action'), $(form).serialize(), function (resp) {
	              (0, _notify2["default"])('success', Translator.trans('site.save_success_hint'));
	              $('#modal').modal('hide');
	            });
	          }
	        }
	      });
	      this.getCustomFields();
	    }
	  }, {
	    key: 'smsCodeValidate',
	    value: function smsCodeValidate() {
	      if ($('.js-captch-num').length > 0) {
	
	        //$('.js-captch-num').find('#getcode_num').attr("src", $("#getcode_num").data("url") + "?" + Math.random());
	
	        $('input[name="captcha_num"]').rules('add', {
	          required: true,
	          alphanumeric: true,
	          es_remote: {
	            type: 'get',
	            callback: function callback(bool) {
	              if (bool) {
	                $('.js-sms-send').removeClass('disabled');
	              } else {
	                $('.js-sms-send').addClass('disabled');
	                $('.js-captch-num').find('#getcode_num').attr("src", $("#getcode_num").data("url") + "?" + Math.random());
	              }
	            }
	          },
	          messages: {
	            required: Translator.trans('site.captcha_code.required'),
	            alphanumeric: Translator.trans('json_response.verification_code_error.message')
	          }
	        });
	
	        $('input[name="sms_code"]').rules('add', {
	          required: true,
	          unsigned_integer: true,
	          es_remote: {
	            type: 'get'
	          },
	          messages: {
	            required: Translator.trans('validate.sms_code_input.message')
	          }
	        });
	      }
	    }
	  }, {
	    key: 'sendSms',
	    value: function sendSms(e) {
	
	      new _smsSender2["default"]({
	        element: '.js-sms-send',
	        url: $('.js-sms-send').data('smsUrl'),
	        smsType: 'sms_bind',
	        dataTo: 'mobile',
	        captchaNum: 'captcha_num',
	        captcha: true,
	        captchaValidated: $('input[name="captcha_num"]').valid(),
	        preSmsSend: function preSmsSend() {
	          var couldSender = true;
	          return couldSender;
	        }
	      });
	    }
	  }, {
	    key: 'getCustomFields',
	    value: function getCustomFields() {
	      for (var i = 1; i <= 5; i++) {
	        $('[name="intField' + i + '"]').rules('add', {
	          required: true,
	          "int": true
	        });
	        $('[name="floatField' + i + '"]').rules('add', {
	          required: true,
	          "float": true
	        });
	        $('[name="dateField' + i + '"]').rules('add', {
	          required: true,
	          date: true
	        });
	      }
	      for (var i = 1; i <= 10; i++) {
	        $('[name="varcharField' + i + '"]').rules('add', {
	          required: true
	        });
	        $('[name="textField' + i + '"]').rules('add', {
	          required: true
	        });
	      }
	    }
	  }, {
	    key: 'changeCaptcha',
	    value: function changeCaptcha(e) {
	      var $code = $(e.currentTarget);
	      $code.attr("src", $code.data("url") + "?" + Math.random());
	    }
	  }]);
	
	  return UserInfoFieldsItemValidate;
	}();
	
	exports["default"] = UserInfoFieldsItemValidate;

/***/ })

});
//# sourceMappingURL=index.js.map