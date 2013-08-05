define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var $form = $('#block-form');
		var $modal = $form.parents('.modal');
        var $table = $('#block-table');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(response){
                    if (response.status == 'ok') {
                        var $html = $(response.html);
                            $table.find('tbody').prepend(response.html);
                            toastr.success('创建成功!');
                        $modal.modal('hide');
                    }
                }, 'json');
            }

        });

        validator.addItem({
            element: '[name="form[title]"]',
            required: true,
            rule: 'maxlength{max:25}'
        });

        validator.addItem({
            element: '[name="form[code]"]',
            required: true,
            rule: 'maxlength{max:25} remote'
        });

	};

});