webpackJsonp(["app/js/open-course/member-sms/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _memberSms = __webpack_require__("5738664e2ae357ed031f");
	
	var _memberSms2 = _interopRequireDefault(_memberSms);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _memberSms2["default"]({
	  element: '#sms-modal-form',
	  formSubmit: '#form-submit'
	});

/***/ }),

/***/ "5738664e2ae357ed031f":
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
	
	var MembarSMS = function () {
	  function MembarSMS(options) {
	    _classCallCheck(this, MembarSMS);
	
	    this.$element = $(options.element);
	    this.formSubmit = options.formSubmit;
	    this.$formSubmit = $(this.formSubmit);
	    this.validator = null;
	
	    this.initEvent();
	    this.initValidator();
	  }
	
	  _createClass(MembarSMS, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '.js-modify-mobile', function (event) {
	        return _this.onModifyMobile(event);
	      });
	      this.$element.on('click', '.js-get-code', function (event) {
	        return _this.onGetCode(event);
	      });
	      this.$element.on('click', '.js-sms-send', function (event) {
	        return _this.onSmsSend(event);
	      });
	      this.$formSubmit.on('click', function (event) {
	        return _this.onFormSubmit(event);
	      });
	    }
	  }, {
	    key: 'onModifyMobile',
	    value: function onModifyMobile(event) {
	      var $this = $(event.currentTarget);
	      $this.hide();
	
	      this.$element.find('input[name="mobile"]').attr('readonly', false);
	      this.$element.find('.form-group').show();
	
	      this.addRules();
	    }
	  }, {
	    key: 'onGetCode',
	    value: function onGetCode(event) {
	      var $this = $(event.currentTarget);
	      $this.attr('src', $this.data('url') + '?' + Math.random());
	    }
	  }, {
	    key: 'onSmsSend',
	    value: function onSmsSend(event) {
	      if (!this.isCanSmsSend()) return;
	
	      var $this = $(event.currentTarget);
	      new _smsSender2["default"]({
	        element: '.js-sms-send',
	        url: $this.data('url'),
	        smsType: 'system_remind',
	        captchaValidated: true,
	        captchaNum: 'captcha_code',
	        captcha: true
	      });
	    }
	  }, {
	    key: 'onFormSubmit',
	    value: function onFormSubmit(event) {
	      if (this.validator.form()) {
	        this.$element.submit();
	      }
	    }
	  }, {
	    key: 'isCanSmsSend',
	    value: function isCanSmsSend() {
	      var isMobile = this.$element.validate().element($('[name="mobile"]'));
	      if (!isMobile) {
	        return false;
	      }
	
	      var isCaptcha = this.$element.validate().element($('[name="captcha_code"]'));
	      if (!isCaptcha) {
	        return false;
	      }
	
	      return true;
	    }
	  }, {
	    key: 'initValidator',
	    value: function initValidator() {
	      var $form = this.$element;
	      this.validator = this.$element.validate({
	        ajax: true,
	        currentDom: this.formSubmit,
	        submitSuccess: function submitSuccess(data) {
	          $form.closest('.modal').modal('hide');
	
	          $("#alert-btn").addClass('hidden');
	          $("#alerted-btn").removeClass('hidden');
	          $('.js-member-num span').text(parseInt(data.number));
	        },
	        submitError: function submitError() {
	          (0, _notify2["default"])('error', Translator.trans(site.form.submit_error));
	        }
	      });
	
	      if (this.$element.find('input[name="mobile"]').attr('readonly') != 'readonly') {
	        this.addRules();
	      }
	    }
	  }, {
	    key: 'addRules',
	    value: function addRules() {
	      $('[name="mobile"]').rules('add', {
	        required: true,
	        phone: true,
	        es_remote: true
	      });
	      $('[name="captcha_code"]').rules('add', {
	        required: true,
	        alphanumeric: true,
	        es_remote: true
	      });
	      $('[name="sms_code_modal"]').rules('add', {
	        required: true,
	        maxlength: 6,
	        minlength: 6,
	        es_remote: true
	      });
	    }
	  }]);
	
	  return MembarSMS;
	}();
	
	exports["default"] = MembarSMS;

/***/ })

});
//# sourceMappingURL=index.js.map