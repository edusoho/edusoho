define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    
	exports.run = function() {
        var $form = $('#category-form');
		var $modal = $form.parents('.modal');
        var $table = $('#category-table');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                $('#category-save-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    $table.find('tbody').replaceWith(html);
                    Notify.success('保存栏目成功！');
				}).fail(function() {
                    Notify.danger("添加栏目失败，请重试！");
                });

            }
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            rule: 'maxlength{max:255}'
        });

        validator.addItem({
            element: '#category-code-field',
            required: true,
            rule: 'alphanumeric not_all_digital remote'
        });

        validator.addItem({
            element: '#category-parentId-field',
            required: true,
            rule: 'integer remote'
        });

        validator.addItem({
            element: '#category-weight-field',
            required: true,
            rule: 'integer'
        });

        $modal.find('.delete-category').on('click', function() {
            if (!confirm('真的要删除该栏目吗？')) {
                return ;
            }

            $.post($(this).data('url'), function(response) {

                if (response.status == 'error') {
                    Notify.danger(response.message);
                } else {
                    window.location.reload();
                    Notify.success(response.message);
                }
                
            }, 'json').error(function(error) {
                Notify.danger("删除栏目失败，请重试！"+error.responseJSON.error.message);
            });

            return false;

        });

	};

});