webpackJsonp(["app/js/settings/setup/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#setup-form');
	var $btn = $('.js-submit-setup-form');
	if ($form.length) {
	  var validator = $form.validate({
	    email: {
	      required: true,
	      es_email: true,
	      es_remote: {
	        type: 'POST'
	      }
	    },
	    nickname: {
	      required: true,
	      minlength: 4,
	      maxlength: 18,
	      nickname: true,
	      chinese_alphanumeric: true,
	      es_remote: {
	        type: 'get'
	      }
	    }
	  });
	
	  $btn.click(function () {
	    if (validator.form()) {
	      $btn.button('loadding');
	      $.post($form.attr('action'), $form.serialize(), function () {
	        (0, _notify2["default"])('success', Translator.trans('settings.setup.set_success.hint'));
	        window.location.href = $btn.data('goto');
	      }).error(function () {
	        $btn.button('reset');
	        (0, _notify2["default"])('danger', Translator.trans('settings.setup.set_failed.hint'));
	      });
	    }
	  });
	}

/***/ })
]);
//# sourceMappingURL=index.js.map