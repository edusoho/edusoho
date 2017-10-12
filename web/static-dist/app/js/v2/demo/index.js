webpackJsonp(["app/js/v2/demo/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$(document).on('click', '[data-toggle="notify"]', function () {
	  (0, _notify2["default"])('danger', '这是警告消息<a href="http://demo.edusoho.com" class="notify-action">操作</a>');
	});

/***/ })
]);
//# sourceMappingURL=index.js.map