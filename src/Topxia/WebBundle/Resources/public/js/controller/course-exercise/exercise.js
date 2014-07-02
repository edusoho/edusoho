define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		require('../course-manage/header').run();

		var $form = $("#exercise-form");

		var validator = new Validator({
			element: $form,
			autoSubmit: false,
			onFormValidated: function(error, results, $form) {
				var isOk = false;
				var url = $form.data('action');

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

	            $.post(url, $form.serialize(), function(response) {
                    Notify.success('保存练习成功!');
                    $('#exercise-save-btn').button('loading');
	            	window.location.href = response;
                }).error(function(){
                    Notify.danger('保存练习失败,请检查题目是否存在！');
                });
           		
            }
		});

		validator.addItem({
			element: '#questionCount',
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

		$form.on('click', '#exercise-save-btn', function() {
    		$(this).button('loading');
		});
	};

});