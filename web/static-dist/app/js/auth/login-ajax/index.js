webpackJsonp(["app/js/auth/login-ajax/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#login-ajax-form');
	var $btn = $('.js-submit-login-ajax');
	var validator = $form.validate({
	  rules: {
	    _username: {
	      required: true
	    },
	    _password: {
	      required: true
	    }
	  }
	});
	
	$btn.click(function (event) {
	  if (validator.form()) {
	    $.post($form.attr('action'), $form.serialize(), function (response) {
	      $btn.button('loading');
	      window.location.reload();
	    }, 'json').error(function (jqxhr, textStatus, errorThrown) {
	      var json = jQuery.parseJSON(jqxhr.responseText);
	      $form.find('.alert-danger').html(json.message).show();
	    });
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map