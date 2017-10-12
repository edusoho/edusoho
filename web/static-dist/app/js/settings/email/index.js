webpackJsonp(["app/js/settings/email/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $btn = $('#submit-btn');
	
	$('#setting-email-form').validate({
	  currentDom: '#submit-btn',
	  ajax: true,
	  rules: {
	    'password': 'required',
	    'email': 'required es_email'
	  },
	  submitSuccess: function submitSuccess(data) {
	    $('#modal').html(data);
	  },
	  submitError: function submitError(data) {
	    (0, _notify2["default"])('danger', Translator.trans(data.responseJSON.message));
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map