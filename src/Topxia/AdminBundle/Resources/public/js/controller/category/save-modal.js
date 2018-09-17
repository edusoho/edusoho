define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    
	exports.run = function() {
        var $form = $('#category-form');
		var $modal = $form.parents('.modal');
        var $table = $('#category-table');

        $("#category-icon-delete").on('click', function(){
            if (!confirm(Translator.trans('admin.category.icon_delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#category-icon-field").html('');
                $form.find('[name=icon]').val('');
                $btn.hide();
                $('#category-icon-field').removeClass('mbm');
                Notify.success(Translator.trans('admin.category.icon_delete_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.category.icon_delete_fail_hint'));
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
                    Notify.success(Translator.trans('admin.category.save_success_hint'));
                    // $table.find('tbody').replaceWith(html);
                    window.location.reload();
				}).fail(function() {
                    Notify.danger(Translator.trans('admin.category.save_fail_hint'));
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
            if (!confirm(Translator.trans('admin.category.delete_hint'))) {
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