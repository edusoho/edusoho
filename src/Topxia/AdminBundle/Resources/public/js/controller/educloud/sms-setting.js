define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	if ($('#sms-form').length>0){	
		// var validator = new Validator({
	 //        element: '#sms-form',
	 //        autoSubmit: false,
	 //        onFormValidated: function(error, results, $form) {
	 //            if (error) {
	 //                return false;
	 //            }
	 //        }

	 //    });

	    // validator.addItem({
	    //     element: '[name="sms-school-name"]',
	    //     required: true,
	    //     rule: 'chinese_alphanumeric byte_minlength{min:2} byte_maxlength{max:8}'
	    // });

		// $('.js-alter-school-name').click(function(){
		// 	$('.js-school-name').removeAttr("readonly");
		// });

		// $('.js-school-name').blur(function(){
		// 	$('.js-school-name').attr("readonly","readonly");
		// });

		// $('.js-school-name').keyup(function(){
		// 	$('#js-sms-school-name').html($('.js-school-name').val());
		// });
		
	}
});