webpackJsonp(["app/js/my/order-refund/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$("#orders-table").on('click', '.js-cancel-refund', function () {
	  var $that = $(this);
	  cd.confirm({
	    title: Translator.trans('user.account.refund_cancel_title'),
	    content: Translator.trans('user.account.refund_cancel_hint'),
	    confirmText: Translator.trans('site.confirm'),
	    cancelText: Translator.trans('site.close'),
	    confirm: function confirm() {
	      $.post($that.data('url'), function () {
	        (0, _notify2["default"])('success', Translator.trans('user.account.refund_cancel_success_hint'));
	        window.location.reload();
	      });
	    }
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map