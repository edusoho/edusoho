webpackJsonp(["app/js/default/header/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _jsCookie = __webpack_require__("fe53252afd7b6c35cb73");
	
	var _jsCookie2 = _interopRequireDefault(_jsCookie);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var PCSwitcher = $('.js-switch-pc');
	var MobileSwitcher = $('.js-switch-mobile');
	if (PCSwitcher.length) {
	  PCSwitcher.on('click', function () {
	    _jsCookie2["default"].set('PCVersion', 1);
	    window.location.reload();
	  });
	}
	if (MobileSwitcher.length) {
	  MobileSwitcher.on('click', function () {
	    _jsCookie2["default"].remove('PCVersion');
	    window.location.reload();
	  });
	}
	
	$('.js-back').click(function () {
	  if (history.length !== 1) {
	    history.go(-1);
	  } else {
	    location.href = '/';
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map