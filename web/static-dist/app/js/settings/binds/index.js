webpackJsonp(["app/js/settings/binds/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('.js-unbind-btn').on('click', function () {
	  var $this = $(this);
	  var url = $this.data('url');
	  cd.confirm({
	    title: Translator.trans('user.settings.unbind_title'),
	    content: Translator.trans('user.settings.unbind_content'),
	    confirmText: Translator.trans('site.confirm'),
	    cancelText: Translator.trans('site.close'),
	    confirm: function confirm() {
	      $.get(url, function (data) {
	        (0, _notify2["default"])('success', Translator.trans(data.message));
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