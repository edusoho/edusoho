define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
		var validator = new Validator({
            element: '#bind-mobile-form',
            autoSubmit: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#submit-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'phone'            
        });

        validator.addItem({
            element: '[name="sms_code"]',
            required: true,
            rule: 'integer minlength{min:6} maxlength{max:6}'            
        });

        refreshTimeLeft = function() { 
        	var leftTime = $('#js-time-left').html();
        	$('#js-time-left').html(leftTime-1);
        	if (leftTime-1 > 0) {
        		setTimeout("refreshTimeLeft()", 1000);
        	}else{
        		$('#js-time-left').html('');
		        $('#js-fetch-btn-text').html('获取短信验证码');
        	}
        }

        $('.js-sms-send').click(function() {
        		var leftTime = $('#js-time-left').html();
        		if (leftTime.length > 0){
        			return false;
        		}
				validator.query('[name="mobile"]').execute(function(error, results, element) {
					if (error){
						return false;
					}
				    var url = $('.js-sms-send').data('url');
		        	var data = {};
		        	data.to = $('[name="mobile"]').val();
		        	data.sms_type = "sms_registration";
		        	$.post(url,data,function(response){
		        		console.log(response);
		        		$('#js-time-left').html('120');
		        		$('#js-fetch-btn-text').html('秒后重新获取');
		        		refreshTimeLeft();
		        	});
				});

        });

	};
});