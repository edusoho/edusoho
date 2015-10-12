define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		if ($('#sms-form').length>0){	

			$('[name="sms_enabled"]').click(function(){
				var status = $('[name="sms_enabled"]:checked').val();
				if (status == 0){
					var registerMode = $('input[name="register-mode"]').val();
					if (registerMode == 'email_or_mobile' || registerMode == 'mobile') {
						
						$('[name="sms_enabled"][value=1]').prop('checked',true);
						Notify.danger("您启用了手机注册模式，不可关闭短信功能！");

					} else {
						$('.js-usage').hide();
					}
					
				}else{

					$('.js-usage').show();
				}
			});
			
		}
	}
	
});