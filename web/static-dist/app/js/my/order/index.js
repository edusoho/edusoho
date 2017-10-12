webpackJsonp(["app/js/my/order/index"],[
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
	
	        setTimeout(function () {
	          window.location.reload();
	        }, 3000);
	      });
	    }
	  });
	});
	
	$("#orders-table").on('click', '.js-cancel', function () {
	  var $that = $(this);
	
	  cd.confirm({
	    title: Translator.trans('user.account.cancel_order_title'),
	    content: Translator.trans('user.account.cancel_order_hint'),
	    confirmText: Translator.trans('site.confirm'),
	    cancelText: Translator.trans('site.close'),
	    confirm: function confirm() {
	      $.post($that.data('url'), function (data) {
	        if (data != true) {
	          (0, _notify2["default"])('danger', Translator.trans('user.account.cancel_order_fail_hint'));
	        }
	        (0, _notify2["default"])('success', Translator.trans('user.account.cancel_order_success_hint'));
	
	        setTimeout(function () {
	          window.location.reload();
	        }, 3000);
	      });
	    }
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map