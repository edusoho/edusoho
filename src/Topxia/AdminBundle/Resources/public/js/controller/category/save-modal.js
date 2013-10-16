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

                $.post($form.attr('action'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    $table.find('tbody').replaceWith(html);
                    Notify.success('保存分类成功！');
				}).fail(function() {
                    Notify.danger("添加分类失败，请重试！");
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
            rule: 'alphanumeric remote'
        });

        validator.addItem({
            element: '#category-weight-field',
            required: false,
            rule: 'integer'
        });

        $modal.find('.delete-category').on('click', function() {
            if (!confirm('真的要删除该分类及其子分类吗？')) {
                return ;
            }

            $.post($(this).data('url'), function(html) {
                $modal.modal('hide');
                $table.find('tbody').replaceWith(html);
            });

        });

	};

});