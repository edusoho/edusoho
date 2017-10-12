webpackJsonp(["app/js/settings/avatar/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _esWebuploader = __webpack_require__("0f84c916401868c4758e");
	
	var _esWebuploader2 = _interopRequireDefault(_esWebuploader);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _esWebuploader2["default"]({
	  element: '#upload-picture-btn',
	  onUploadSuccess: function onUploadSuccess(file, response) {
	    var url = $("#upload-picture-btn").data("gotoUrl");
	    (0, _notify2["default"])('success', Translator.trans('site.upload_success_hint'), 1);
	    document.location.href = url;
	  }
	});
	
	//论坛头像
	$('.use-partner-avatar').on('click', function () {
	  var $this = $(this);
	  var goto = $this.data('goto');
	
	  $.post($this.data('url'), { imgUrl: $this.data('imgUrl') }, function () {
	    window.location.href = goto;
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map