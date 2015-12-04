define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

	exports.run = function() {
		var $modal = $('#modal');
		var $form = $('#unbind-form');
		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $modal.modal('hide');
                var payAgreementId = $("input[name='payAgreementId']").val();
            	$.post($form.attr('action'),$form.serialize(),function(response){
		            if(response.success){
		            	$('#unbind-bank-'+payAgreementId).remove();
		            	Notify.success(response.message);
		            }else{
		            	Notify.danger(response.message);
		            }
		        });
            }
        });


        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'phone',
            display: '手机号码'
        })

	};
});