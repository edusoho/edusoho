define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
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
                
                $('#block-save-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(response){
                    if (response.status == 'ok') {
                        var $html = $(response.html);
                            if ($form.data('id') == 0 ) {
                                $table.find('tbody').prepend(response.html);
                                Notify.success(Translator.trans('admin.block.create_success_hint'));
                            } else {
                                $('#' + $html.attr('id')).replaceWith($html);
                                Notify.success(Translator.trans('admin.block.edit_success_hint'));
                            };
                        $modal.modal('hide');
                    }
                }, 'json');
            }

        });

        $form.find('input[name="mode"]:radio').on('change', function() {

            if ($(this).val() == 'template') {
                $("#template").show();
            } else {
                $("#template").hide();
            };
        });

        $form.find('input[name="mode"]:checked').trigger('change');

        validator.addItem({
            element: '[name="title"]',
            required: true,
            rule: 'maxlength{max:25}'
        });

        validator.addItem({
            element: '[name="code"]',
            required: true,
            rule: 'maxlength{max:30} alphabet_underline remote'
            // remote
        });

	};

});