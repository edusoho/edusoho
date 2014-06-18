define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		require('./header').run();

		var $form = $("#exercise-form");

		var validator = new Validator({
			element: $form,
			autoSubmit: false,
			onFormValidated: function(error, results, $form) {
				var isOk = false;
				var target = $form.data('target');

                if (error) {
                    return ;
                }

                $form.find('input[type=checkbox]').each(function() {
	                if($(this).attr('checked')) {
	                	isOk = true;
	                };
	            });

	            if (!isOk) {
	            	Notify.danger('请至少选择一个题型范围。');
	            	return isOk;
	            }

	            $.post($form.attr('action'), $form.serialize(), function() {
                    Notify.success('添加练习成功!');
                    /*window.location.href = target;*/
                }).error(function(){
                    Notify.danger('添加练习失败!');
                });
           		
            }
		});

		validator.addItem({
			element: '#question_number',
			required: true,
			rule: 'integer'
		});

		$form.on('click', 'input[type=checkbox]', function() {
			if($(this).attr("checked")){
				$(this).removeAttr('checked');
			} else {
				$(this).attr('checked', 'checked');
			}
		});
	};

});