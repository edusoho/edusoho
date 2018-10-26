define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
	exports.run = function() {
		var $form = $('#tag-form');
		var $modal = $form.parents('.modal');
        var $table = $('#tag-table');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#tag-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html){
                    var $html = $(html);
                    if ($table.find( '#' +  $html.attr('id')).length > 0) {
                        $('#' + $html.attr('id')).replaceWith($html);
                        Notify.success(Translator.trans('admin.tag.update_tag_success_hint'));
                    } else {
                        $table.find('tbody').prepend(html);
                        Notify.success(Translator.trans('admin.tag.add_tag_success_hint'));
                    }
                    $modal.modal('hide');
				});

            }
        });

        validator.addItem({
            element: '#tag-name-field',
            required: true,
            rule: 'remote byte_maxlength{max:20}'
        });

        $modal.find('.delete-tag').on('click', function() {
            if (!confirm(Translator.trans('admin.tag.single_delete_hint'))) {
                return ;
            }

            var trId = '#tag-tr-' + $(this).data('tagId');
            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $table.find(trId).remove();
            });

        });

	};




});