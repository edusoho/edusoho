define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	var validator = new Validator({
        element: '#apply-sms-form',
        autoSubmit: false,
        onFormValidated: function(error, results, $form) {
            if (error) {
                return false;
            }
        }

    });

    validator.addItem({
        element: '[name="name"]',
        required: true,
        rule: 'chinese_alphanumeric byte_minlength{min:2} byte_maxlength{max:8}'
    });

	$('#js-submit').click(function(){
		var url = $('#apply-sms-form').data('url');
		var data = {};
		data.name = $("[name='name']").val();
		$('#js-submit').addClass('disabled');
		$.post(
			url,
			data,
			function(response){
				if (response['ACK'] == 'ok') {
					// window.location.reload();
				}
				$('#js-submit').removeClass('disabled');
			}
		);	
		$('#js-submit').removeClass('disabled');
	});

});