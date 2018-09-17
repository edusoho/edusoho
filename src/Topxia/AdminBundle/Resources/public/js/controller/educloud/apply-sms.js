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

	var refresh = function () {
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
	        rule: 'chinese_alphanumeric byte_minlength{min:2} byte_maxlength{max:16}',
	        errormessageByte_maxlength:Translator.trans('admin.edu_cloud.validate_old.title.message')
    	});

		$('#js-submit').unbind('click');	
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

		$('[name="name"]').unbind('keyup');
		$('[name="name"]').keyup(function(){
			$('#js-school-name').html($('[name="name"]').val());
		});

		// $('[name="name"]').unbind('blur');
		$('[name="name"]').blur(function(){
			$('#js-school-name').html($('[name="name"]').val());
		});
	}

    $('#modal').on('shown.bs.modal', function () {
		setTimeout(refresh,500); 
    });

    refresh();

});