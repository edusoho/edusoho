webpackJsonp(["app/js/settings/security/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('#send-verify-email').click(function () {
	  var $btn = $(this);
	  $.post($btn.data('url')).done(function (data) {
	    $('#modal').html(data).modal('show');
	    $btn.button('reset');
	  }).fail(function (data) {
	    $btn.button('reset');
	    (0, _notify2["default"])('danger', Translator.trans(data.responseJSON.message));
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map