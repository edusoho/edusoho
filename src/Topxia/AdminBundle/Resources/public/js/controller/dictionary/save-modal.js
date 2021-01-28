define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);

	exports.run = function() {
        var $form = $('#dictionary-form');
		var $modal = $form.parents('.modal');
        var $table = $('#dictionary-table');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $.post($form.attr('action'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    location.reload();
                    if ($form.data('flag') == 'edit') {
                        Notify.success(Translator.trans('admin.dictionary.update_success_hint'));
                    } else {
                        Notify.success(Translator.trans('admin.dictionary.create_success_hint'));
                    }

                }).fail(function() {
                    if ($foem.data('flag') == 'edit') {
                        Notify.danger(Translator.trans('admin.dictionary.update_fail_hint'));
                    } else {
                        Notify.danger(Translator.trans('admin.dictionary.create_fail_hint'));
                    }

                });

            }
        });

        validator.addItem({
            element: '#dictionary-name-field',
            required: true,
            rule: 'remote'
        });


        validator.addItem({
            element: '#dictionary-weight-field',
            required: false,
            rule: 'integer'
        });

	};

});