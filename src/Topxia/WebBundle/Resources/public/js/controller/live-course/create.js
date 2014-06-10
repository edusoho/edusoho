define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");

	exports.run = function() {

        var $modal = $('#live-lesson-form').parents('.modal');
        var validator = new Validator({
            element: '#live-lesson-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
            	if (error) {
            		return false;
            	}
				$.post($form.attr('action'), $form.serialize(), function(html) {
					$modal.modal('hide');
					Notify.success('保存成功');
                    // var $tr = $(html);
                    // $('#' + $tr.attr('id')).replaceWith($tr);
				}).error(function(){
					Notify.danger('操作失败');
				});
            }
        });

        validator.addItem({
            element: '[name=startTime]',
            rule: 'date'
        });   

        validator.addItem({
            element: '[name=endTime]',
            rule: 'date'
        });

        validator.addItem({
            element: '#live-title-field',
        });

        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
	};

});