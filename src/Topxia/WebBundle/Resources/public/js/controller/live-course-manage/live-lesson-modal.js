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

                var $panel = $('.lesson-manage-panel');

				$.post($form.attr('action'), $form.serialize(), function(html) {

                    var id = '#' + $(html).attr('id'),
                    $item = $(id);
                    
                    if ($item.length) {
                        $item.replaceWith(html);
                        Notify.success('课时已保存');
                    } else {
                        $panel.find('.empty').remove();
                        $("#course-item-list").append(html);
                        Notify.success('添加直播课时成功');
                    }
                    $modal.modal('hide');

				}).error(function(){
					Notify.danger('操作失败');
				});
            }
        });

        Validator.addRule('romote_check',
            function(options, commit) {
                var element = $('#live_lesson_time_check');
                var startTime = $('[name=startTime]').val();
                var length = $('[name=length]').val();
                if(startTime && length) {
                    url = element.data('url');
                    $.get(url, {startTime:startTime,length:length}, function(response) {
                        commit(response.success, response.message);
                    }, 'json');
                }else{
                    return true;
                }
            });

        validator.addItem({
            element: '#live-title-field',
            required: true
        });

        validator.addItem({
            element: '[name=startTime]',
            required: true,
            errormessageRequired: '请输入直播的开始时间'
        });   

        validator.addItem({
            element: '[name=length]',
            required: true,
            rule:'integer romote_check',
            errormessageRequired: '请输入时长'
        });

        $("[name=startTime]").datetimepicker({
        }).on('hide', function(ev){

            validator.query('[name=startTime]').execute();
        });
	};

});