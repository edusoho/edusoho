webpackJsonp(["app/js/card/index/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('a[role=filter-change]').click(function (event) {
	  window.location.href = $(this).data('url');
	});
	
	$('.receive-modal').click();
	$('body').on('click', '.money-card-use', function () {
	  $('body').off('click', '.money-card-use');
	  var url = $(this).data('url');
	  var target_url = $(this).data('target-url');
	  var coin = $(this).prev().text();
	
	  $.post(url, function (response) {
	    (0, _notify2["default"])('success', Translator.trans('学习卡已使用，充值' + coin + '虚拟币成功，可前往【账户中心】-【我的账户】查看充值情况。'));
	    setTimeout("window.location.href = '" + target_url + "'", 2000);
	  }).error(function () {
	    (0, _notify2["default"])('danger', Translator.trans('失败！'));
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map