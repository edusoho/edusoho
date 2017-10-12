webpackJsonp(["app/js/settings/password-modal/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#settings-password-form');
	var $modal = $('#modal');
	
	var validator = $form.validate({
	  rules: {
	    'form[newPassword]': {
	      required: true,
	      minlength: 5,
	      maxlength: 20
	    },
	    'form[confirmPassword]': {
	      required: true,
	      equalTo: '#form_newPassword'
	    }
	  }
	});
	
	$('.js-submit-form').off('click');
	$('.js-submit-form').click(function () {
	  if (validator.form()) {
	    var data = $form.serialize();
	    var targetUrl = $form.attr("action");
	    $.post(targetUrl, data, function (html) {
	      $modal.html(html);
	    });
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map