define(function(require, exports, module) {
    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");

	exports.run = function() {

        var $modal = $('#live-lesson-form').parents('.modal');
        var $content = $("#live-lesson-content-field");
        var now = new Date();
        var validator = new Validator({
            
            element: '#live-lesson-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
            	if (error) {
            		return false;
            	}
                $('#live-course-create-btn').button('submiting').addClass('disabled');
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
                var length = $('[name=timeLength]').val();

                if(startTime && length) {
                    url = element.data('url');
                    $.get(url, {startTime:startTime,length:length}, function(response) {
                        commit(response.success, response.message);
                    }, 'json');
                }else{
                    return true;
                }
            });

        Validator.addRule('live_date_check',
            function() {

                var thisTime = $('[name=startTime]').val();
                thisTime = thisTime.replace(/-/g,"/");
                thisTime = Date.parse(thisTime)/1000;
                var nowTime = Date.parse(new Date())/1000;

                if (nowTime <= thisTime) {
                    return true;
                }else{
                    return false;
                }
            },"请输入一个晚于现在的时间"

        );

        validator.addItem({
            element: '#live-title-field',
            required: true
        });

        validator.addItem({
            element: '[name=startTime]',
            required: true,
            rule:'live_date_check',
            errormessageRequired: '请输入直播的开始时间'
        });   

        validator.addItem({
            element: '[name=timeLength]',
            required: true,
            rule:'integer romote_check',
            errormessageRequired: '请输入时长'
        });
     
        $('[name=startTime]').datetimepicker('setStartDate', now);
        $("[name=startTime]").datetimepicker({
        }).on('hide', function(ev){
            validator.query('[name=startTime]').execute();
        });

        var editor = EditorFactory.create('#live_lesson-content-field', 'standard', {extraFileUploadParams:{group:'course'}, height: '300px'});
        
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
            var z = editor.html();
            var x = editor.html().match(/<embed[\s\S]*?\/>/g);
            if (x) {
                for (var i = x.length - 1; i >= 0; i--) {
                   var y = x[i].replace(/\/>/g,"wmode='Opaque' \/>");
                   var z =  z.replace(x[i],y);
                };
            }
            $content.val(z);
        });
    };

});