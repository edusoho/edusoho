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
                        Notify.success(Translator.trans('标签更新成功！'));
                    } else {
                        $table.find('tbody').prepend(html);
                        Notify.success(Translator.trans('标签添加成功!'));
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
            if (!confirm(Translator.trans('真的要删除该标签吗？'))) {
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