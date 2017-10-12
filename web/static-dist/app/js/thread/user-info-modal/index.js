webpackJsonp(["app/js/thread/user-info-modal/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#join-event-form');
	
	
	$form.validate({
	  ajax: true,
	  currentDom: '#join-event-btn',
	  rules: {
	    truename: {
	      required: true,
	      chinese: true,
	      byte_minlength: 4,
	      byte_maxlength: 10
	    },
	    mobile: {
	      required: true,
	      phone: true
	    }
	  },
	  submitSuccess: function submitSuccess() {
	    (0, _notify2["default"])('success', Translator.trans('site.save_success_hint'));
	    window.location.reload();
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map