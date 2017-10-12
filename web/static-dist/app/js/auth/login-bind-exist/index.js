webpackJsonp(["app/js/auth/login-bind-exist/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#bind-exist-form');
	var $btn = $form.find('#set-bind-exist-btn');
	var validator = $form.validate({
	  rules: {
	    emailOrMobile: {
	      required: true,
	      email_or_mobile: true
	    },
	    password: {
	      required: true
	    }
	  }
	});
	
	$btn.click(function () {
	  if (validator.form()) {
	    $btn.button('loading');
	    $("#bind-exist-form-error").hide();
	    $.post($form.attr('action'), $form.serialize(), function (response) {
	
	      console.log(response);
	      if (!response.success) {
	        $("#bind-exist-form-error").html(response.message).show();
	        $btn.button('reset');
	        return;
	      }
	      (0, _notify2["default"])('success', Translator.trans('auth.login_bind_exist.bind_success_hint'));
	      window.location.href = response._target_path;
	    }, 'json').fail(function () {
	      (0, _notify2["default"])('danger', Translator.trans('auth.login_bind_exist_bind_failed_hint'));
	    }).always(function () {
	      $btn.button('reset');
	    });
	  }
	});
	
	$.validator.addMethod("email_or_mobile", function (value, element, params) {
	  var emailOrMobile = value;
	  var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	  var reg_mobile = /^1\d{10}$/;
	  var result = false;
	  var isEmail = reg_email.test(emailOrMobile);
	  var isMobile = reg_mobile.test(emailOrMobile);
	  if (isMobile) {
	    $(".email_mobile_msg").removeClass('hidden');
	  } else {
	    $(".email_mobile_msg").addClass('hidden');
	  }
	  if (isEmail || isMobile) {
	    result = true;
	  }
	  return this.optional(element) || result;
	}, Translator.trans('auth.login_bind_exist_bind_validate_hint'));

/***/ })
]);
//# sourceMappingURL=index.js.map