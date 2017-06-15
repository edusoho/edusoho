webpackJsonp(["app/js/settings/pay-password-modal/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import notify from 'common/notify';
	
	var PayPasswordModal = function () {
	  function PayPasswordModal(props) {
	    _classCallCheck(this, PayPasswordModal);
	
	    this.element = $(props.element);
	    this.currentDom = props.currentDom;
	    this.init();
	  }
	
	  _createClass(PayPasswordModal, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	      this.validate();
	    }
	  }, {
	    key: 'validate',
	    value: function validate() {
	      var currentDom = this.currentDom;
	      var validator = this.element.validate({
	        ajax: true,
	        currentDom: currentDom,
	        rules: {
	          'form[currentUserLoginPassword]': {
	            required: true,
	            passwordCheck: true
	          },
	          'form[newPayPassword]': {
	            required: true,
	            maxlength: 20,
	            minlength: 5
	          },
	          'form[confirmPayPassword]': {
	            required: true,
	            equalTo: '#form_newPayPassword'
	          }
	        },
	        submitError: function submitError(data) {
	          notify('danger', '未成功设置支付密码');
	        },
	        submitSuccess: function submitSuccess(data) {
	          notify('success', data.message);
	          setTimeout(function () {
	            window.location.reload();
	          }, 1000);
	        }
	      });
	      return validator;
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      $(this.currentDom).on('click', function () {
	        if (_this.validate().form()) {
	          _this.element.submit();
	        }
	      });
	    }
	  }]);
	
	  return PayPasswordModal;
	}();
	
	new PayPasswordModal({
	  element: '#settings-pay-password-form',
	  currentDom: '.js-submit-form'
	});

/***/ })
]);