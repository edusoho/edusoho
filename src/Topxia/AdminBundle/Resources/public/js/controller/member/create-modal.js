define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

	exports.run = function() {

        var $modal = $('#member-create-form').parents('.modal');
        var validator = new Validator({
            element: '#member-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
            	if (error) {
            		return false;
            	}
				$.post($form.attr('action'), $form.serialize(), function(html) {
					$modal.modal('hide');
					Notify.success('新用户添加成功');
                    window.location.reload();
				}).error(function(){
					Notify.danger('新用户添加失败');
				});

            }
        });

        validator.addItem({
            element: '[id="nickname"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
        });

        validator.addItem({
            element: '[id="deadline"]',
            required: true,
            rule: 'date'
        });
	};

});