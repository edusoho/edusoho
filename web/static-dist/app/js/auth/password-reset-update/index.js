webpackJsonp(["app/js/auth/password-reset-update/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#password-reset-update-form');
	var validator = $form.validate({
	  rules: {
	    'form[password]': {
	      required: true,
	      minlength: 5,
	      maxlength: 20
	    },
	    'form[confirmPassword]': {
	      required: true,
	      equalTo: '#form_password'
	    }
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map