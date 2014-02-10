define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var $form = $('#course-form');
		var $modal = $form.parents('.modal');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                $.post($form.attr('action'), $form.serialize(), function(response){
                    if (response.status == 'ok') {
                        Notify.success('保存成功!');
                        $modal.modal('hide');
                    }
                }, 'json');
            }

        });

        validator.addItem({
            element: '[name="adCommission"]',
            required: true,
            rule: 'currency maxlength{max:25}'
        });

        validator.addItem({
            element: '[name="adCommissionDay"]',
            required: true,
            rule: 'integer maxlength{max:25}'
            // remote
        });

        $('[name=adCommissionType]').on('click', function(){
            var val = $('input[name="adCommissionType"]:checked').val();
            if (val == 'quota') {
                $('.quota-text').removeClass('hidden');
                $('.ratio-text').addClass('hidden');
            } else if (val == 'ratio') {
                $('.quota-text').addClass('hidden');
                $('.ratio-text').removeClass('hidden');
            }
        });


	};

});