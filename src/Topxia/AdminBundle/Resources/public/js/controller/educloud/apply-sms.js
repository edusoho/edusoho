define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	validator = new Validator({
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

	validateSchoolName = function () {
    	validator.destroy();
		validator = new Validator({
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
	}

    $('#modal').on('shown.bs.modal', function () {
		setTimeout('validateSchoolName()',500); 
    });

    $('#modal').on('hidden.bs.modal', function () {
    	validator.removeItem({
	        element: '[name="name"]'
    	});
    });

	$('#js-submit').click(function(){
		validator.execute(function(error, results, element) {
            if (error) {
                return false;
            }               
        
			var url = $('#apply-sms-form').data('url');
			var data = {};
			data.name = $("[name='name']").val();
			$('#js-submit').addClass('disabled');
			$.post(
				url,
				data,
				function(response){
					$('#js-submit').removeClass('disabled');
					if (response['ACK'] == 'ok') {
						window.location.reload();
					}
				}
			);
		});
	});

	$('[name="name"]').keyup(function(){
		$('#js-school-name').html($('[name="name"]').val());
	});

	$('[name="name"]').blur(function(){
		$('#js-school-name').html($('[name="name"]').val());
	});

});