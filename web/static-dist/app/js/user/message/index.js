webpackJsonp(["app/js/user/message/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _message = __webpack_require__("09eb1f9807af90690645");
	
	var _message2 = _interopRequireDefault(_message);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _message2["default"]({
	  element: '#message-create-form'
	});

/***/ }),

/***/ "09eb1f9807af90690645":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Message = function () {
	  function Message(options) {
	    _classCallCheck(this, Message);
	
	    this.$element = $(options.element);
	    this.validator();
	  }
	
	  _createClass(Message, [{
	    key: 'validator',
	    value: function validator() {
	      var $element = this.$element;
	      $element.validate({
	        rules: {
	          'message[receiver]': {
	            required: true,
	            es_remote: true,
	            chinese_alphanumeric: true
	          },
	          'message[content]': {
	            required: true,
	            maxlength: 500
	          }
	        },
	        ajax: true,
	        submitSuccess: function submitSuccess() {
	          (0, _notify2["default"])('success', Translator.trans('私信发送成功'));
	          $element.closest('.modal').modal('hide');
	        },
	        submitError: function submitError() {
	          (0, _notify2["default"])('danger', Translator.trans('私信发送失败，请重试！'));
	        }
	      });
	    }
	  }]);
	
	  return Message;
	}();
	
	exports["default"] = Message;

/***/ })

});
//# sourceMappingURL=index.js.map