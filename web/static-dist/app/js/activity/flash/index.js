webpackJsonp(["app/js/activity/flash/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _esSwfobject = __webpack_require__("c04c1b91e3806f24595a");
	
	var _esSwfobject2 = _interopRequireDefault(_esSwfobject);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $el = $('#flash-player');
	
	if (!_esSwfobject2["default"].hasFlashPlayerVersion('11')) {
	  var html = '\n    <div class="alert alert-warning alert-dismissible fade in" role="alert">\n      <button type="button" class="close" data-dismiss="alert" aria-label="Close">\n      <span aria-hidden="true">\xD7</span>\n      </button>\n      ' + Translator.trans('site.flash_not_install_hint') + '\n    </div>';
	  $el.html(html);
	  $el.show();
	} else {
	  _esSwfobject2["default"].embedSWF($el.data('uri'), 'flash-player', '100%', '100%', "9.0.0", null, null, {
	    wmode: 'opaque',
	    allowFullScreen: 'true'
	  });
	}

/***/ })
]);
//# sourceMappingURL=index.js.map