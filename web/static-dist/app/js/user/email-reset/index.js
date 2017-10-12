webpackJsonp(["app/js/user/email-reset/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#reset-email-form');
	var $btn = $('#next-btn');
	var validator = $form.validate({
	  rules: {
	    email: {
	      required: true,
	      es_email: true,
	      es_remote: {
	        type: 'get'
	      }
	    },
	    password: {
	      required: true,
	      minlength: 5,
	      maxlength: 20
	    }
	  }
	});
	
	$btn.click(function () {
	  if (validator.form()) {
	    $btn.button('loadding');
	    $form.submit();
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map