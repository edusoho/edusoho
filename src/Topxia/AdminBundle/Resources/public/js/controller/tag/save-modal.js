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

                $.post($form.attr('action'), $form.serialize(), function(response){
					if (response.status == 'ok') {
                        var $html = $(response.html);
                        if ($table.find( '#' +  $html.attr('id')).length > 0) {
                            $('#' + $html.attr('id')).replaceWith($html);
                            Notify.success('更新成功！');
                        } else {
                            $table.find('tbody').prepend(response.html);
                            Notify.success('提交成功!');
                        }
                        $modal.modal('hide');
					} else {
						var errorMsg = '添加失败：' + ((response.error && response.error.message) ? response.error.message : '');
						Notify.danger(errorMsg);
					}
				}, 'json');

                

            }
        });

        validator.addItem({
            element: '[name="form[name]"]',
            required: true,
            rule: 'maxlength{max:25} remote'
        });

	};

});