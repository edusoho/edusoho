define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    
	exports.run = function() {
        var $form = $('#category-form');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#category-save-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html){
                    Notify.success('保存成功！');
                    $('#category-save-btn').button('reset').removeClass('disabled');
				}).fail(function() {
                    Notify.danger("保存失败，请重试！");
                    $('#category-save-btn').button('reset').removeClass('disabled');
                });

            }
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            rule: 'maxlength{max:100}'
        });

        validator.addItem({
            element: '#category-code-field',
            required: true,
            rule: 'alphanumeric not_all_digital remote'
        });

        validator.addItem({
            element: '#category-weight-field',
            required: false,
            rule: 'integer'
        });

	};

});