webpackJsonp(["app/js/card/card-receive/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	if ($('a').hasClass('money-card-use')) {
	  var url = $('.money-card-use').data('url');
	  var target_url = $('.money-card-use').data('target-url');
	  var coin = $('.card-coin-val').val();
	
	  $.post(url, function (response) {
	    (0, _notify2["default"])('success', Translator.trans('card.card_receive_success_hint', { coin: coin }));
	    setTimeout("window.location.href = '" + target_url + "'", 2000);
	  }).error(function () {
	    (0, _notify2["default"])('danger', Translator.trans('card.card_receive_failed_hint'));
	  });
	}

/***/ })
]);
//# sourceMappingURL=index.js.map