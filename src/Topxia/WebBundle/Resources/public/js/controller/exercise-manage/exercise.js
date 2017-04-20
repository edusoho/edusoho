define(function(require, exports, module) {

	var Widget = require('widget');
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		require('../../../../topxiaweb/js/controller/course-manage/header').run();

		var $form = $("#exercise-form");

		var validator = new Validator({
			element: $form,
			autoSubmit: false,
			onFormValidated: function(error, results, $form) {
				var isOk = false;
				var url = $form.data('action');
				var questionCountIsOk = true;

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

				$.ajax($form.data('buildCheckUrl'), {
	                type: 'POST',
	                async: false,
	                data: $form.serialize(),
	                dataType: 'json',
	                success: function(response) {
	                	if (response.status != 'yes') {
	                		var lessNum = response.lessNum;
	                		Notify.danger('课程题库题目数量不足，无法生成练习：还缺少' + lessNum + '题');
	                    	questionCountIsOk = false;
	                	};
	                }
	            });

	            if (!questionCountIsOk) {
	            	return ;
	            };

	            $.post(url, $form.serialize(), function(response) {
                    Notify.success('保存练习成功!');
                    $('#exercise-save-btn').button('loading').addClass('disabled');
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
	};

});