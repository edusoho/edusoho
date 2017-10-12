webpackJsonp(["app/js/cashier/modal/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('.js-confirm-btn').on('click', function (event) {
	  var $target = $(event.currentTarget);
	
	  $.get($target.data('url'), function (resp) {
	    if (resp.isPaid) {
	      location.href = resp.redirectUrl;
	    } else {
	      (0, _notify2["default"])('danger', Translator.trans('cashier.confirm.fail_message'));
	      $('#modal').modal('hide');
	    }
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map