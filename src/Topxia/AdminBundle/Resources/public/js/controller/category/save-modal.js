define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    
	exports.run = function() {
        var $form = $('#category-form');
		var $modal = $form.parents('.modal');
        var $table = $('#category-table');

        $("#category-icon-delete").on('click', function(){
            if (!confirm(Translator.trans('确认要删除图标吗？'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#category-icon-field").html('');
                $form.find('[name=icon]').val('');
                $btn.hide();
                $('#category-icon-field').removeClass('mbm');
                Notify.success(Translator.trans('删除分类图标成功！'));
            }).error(function(){
                Notify.danger(Translator.trans('删除分类图标失败！'));
            });
        });

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#category-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize()).done(function(html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('保存分类成功！'));
                    // $table.find('tbody').replaceWith(html);
                    window.location.reload();
				}).fail(function() {
                    Notify.danger(Translator.trans('添加分类失败，请重试！'));
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

        $modal.find('.delete-category').on('click', function() {
            if (!confirm(Translator.trans('真的要删除该分类及其子分类吗？'))) {
                return ;
            }

            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                window.location.reload();
                // $table.find('tbody').replaceWith(html);
            });

        });

	};

});