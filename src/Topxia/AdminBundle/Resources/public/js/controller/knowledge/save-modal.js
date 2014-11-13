define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    require('webuploader');
    
	exports.run = function() {
        var $form = $('#knowledge-form');
		var $modal = $form.parents('.modal');
        var $list = $('.knowledge-list');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#knowledge-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    $list.find('ul:first').append(html);
                    Notify.success('保存知识点成功！');
				}).fail(function() {
                    Notify.danger("添加知识点失败，请重试！");
                });

            }
        });

        validator.addItem({
            element: '#knowledge-name-field',
            required: true,
            rule: 'maxlength{max:100}'
        });

        validator.addItem({
            element: '#knowledge-code-field',
            required: true,
            rule: 'alphanumeric not_all_digital remote'
        });

        validator.addItem({
            element: '#knowledge-weight-field',
            required: false,
            rule: 'integer'
        });

	};

});