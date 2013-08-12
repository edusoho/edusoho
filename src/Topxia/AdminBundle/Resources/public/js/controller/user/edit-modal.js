define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	exports.run = function() {

        var $modal = $('#user-edit-form').parents('.modal');

        var validator = new Validator({
            element: '#user-edit-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
            	if (error) {
            		return false;
            	}

				$.post($form.attr('action'), $form.serialize(), function(html) {
					$modal.modal('hide');
					Notify.success('用户信息保存成功');
					var $tr = $(html);
					$('#' + $tr.attr('id')).replaceWith($tr);
				}).error(function(){
					Notify.danger('操作失败');
				});
            }
        });


        $('.user-lock-btn, .user-unlock-btn').click(function(e){
            e.preventDefault();
            var $btn = $(this);
            if(!confirm('真的要' + $btn.attr('title') + '吗？')) {
                return ;
            }

            $.post($(this).attr('href'), function(html){
                $modal.modal('hide');
                Notify.success($btn.attr('title') + '成功！');
                 var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger($btn.attr('title') + '失败');
            });

        });

        $('#password-reset').click(function(){
            $.post($(this).data('url'),function(response){
                Notify.success('邮件发送成功！');                
            }).error(function(){
                Notify.danger('邮件发送失败');
            });
        });

        $('#register-email-send').click(function(){
            $.post($(this).data('url'),function(response){
                Notify.success('邮件发送成功！');                
            }).error(function(){
                Notify.danger('邮件发送失败');
            });
        });

	};

});