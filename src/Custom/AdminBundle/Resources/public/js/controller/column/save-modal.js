define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
	exports.run = function() {
		var $form = $('#column-form');
		var $modal = $form.parents('.modal');
        var $table = $('#column-table');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#column-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html){
                    var $html = $(html);
                    if ($table.find( '#' +  $html.attr('id')).length > 0) {
                        $('#' + $html.attr('id')).replaceWith(html);
                        Notify.success('专栏更新成功！');
                    } else {
                        $table.find('tbody').prepend(html);
                        Notify.success('专栏添加成功!');
                    }

                    var $tr = $(html);
                    $('#' + $tr.attr('id')).replaceWith($tr);
                    $modal.modal('hide');
				});

            }
        });

        validator.addItem({
            element: '#column-name-field',
            required: true,
            rule: 'remote'
        });
        validator.addItem({
            element: '#column-code-field',
            required: true,
            rule: 'remote'
        });
        validator.addItem({
            element: '#column-weight-field',
            required: true,
            rule: 'integer'
        });
        

        $modal.find('.delete-column').on('click', function() {
            if (!confirm('真的要删除该专栏吗？')) {
                return ;
            }

            var trId = '#column-tr-' + $(this).data('columnId');
            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $table.find(trId).remove();
            });

        });

	};




});