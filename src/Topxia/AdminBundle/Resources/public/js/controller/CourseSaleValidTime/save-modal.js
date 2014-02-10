define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");

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

        $("#course-saleValidTime-field").datetimepicker({
            format: "yyyy-mm-dd hh:ii"
        }); 

        validator.addItem({
            element: '[name="saleValidTime"]',
            required: true,
        });

      


	};

});