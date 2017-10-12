webpackJsonp(["app/js/auth/login/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#login-form');
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
	$('#login-form').keypress(function (e) {
		if (e.which == 13) {
			$('.js-btn-login').trigger('click');
			e.preventDefault(); // Stops enter from creating a new line
		}
	});
	
	$('.js-btn-login').click(function (event) {
		if (validator.form()) {
			$(event.currentTarget).button('loadding');
			$form.submit();
		}
	});
	
	$('.receive-modal').click();

/***/ })
]);
//# sourceMappingURL=index.js.map