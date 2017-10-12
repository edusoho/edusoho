webpackJsonp(["app/js/group/logo/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _esWebuploader = __webpack_require__("0f84c916401868c4758e");
	
	var _esWebuploader2 = _interopRequireDefault(_esWebuploader);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _esWebuploader2["default"]({
		element: '#group-save-btn',
		onUploadSuccess: function onUploadSuccess(file, response) {
			var url = $("#group-save-btn").data("gotoUrl");
			(0, _notify2["default"])('success', Translator.trans('site.upload_success_hint'));
			document.location.href = url;
		}
	});

/***/ })
]);
//# sourceMappingURL=index.js.map