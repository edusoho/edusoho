define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    
	exports.run = function() {

        var $modal = $('#member-modal-form').parents('.modal');
        var validator = new Validator({
            element: '#member-modal-form',
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
            element: '#nickname',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
        });

        validator.addItem({
            element: '#deadline',
            required: true,
            rule: 'date'
        });

        validator.addItem({
            element: '[name=levelId]',
            required: true,
            errormessageRequired: '请选择会员类型!'
        });

        $("#deadline").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
	};

});