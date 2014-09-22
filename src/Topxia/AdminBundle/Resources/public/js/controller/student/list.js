define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $table = $('#user-table');
        var $form = $('#user-search-form');

		$table.on('click', '.lock-user, .unlock-user', function() {
			var $trigger = $(this);

			if (!confirm('真的要' + $trigger.attr('title') + '吗？')) {
				return ;
			}

            $.post($(this).data('url'), function(html){
                Notify.success($trigger.attr('title') + '成功！');
                 var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger($trigger.attr('title') + '失败');
            });
		});


        $form.on('click','#class',function(){
            $.get($(this).data('url'), function(html){
                $("#modal").modal('show');
                $("#modal").html(html);
            }).error(function(){
            });
        });
		// $table.on('click', '.send-passwordreset-email', function(){
  //           Notify.info('正在发送密码重置验证邮件，请稍等。', 60);
  //           $.post($(this).data('url'),function(response){
  //               Notify.success('密码重置验证邮件，发送成功！');
  //           }).error(function(){
  //               Notify.danger('密码重置验证邮件，发送失败');
  //           });
		// });

		// $table.on('click', '.send-emailverify-email', function(){
  //           Notify.info('正在发送Email验证邮件，请稍等。', 60);
  //           $.post($(this).data('url'),function(response){
  //               Notify.success('Email验证邮件，发送成功！');
  //           }).error(function(){
  //               Notify.danger('Email验证邮件，发送失败');
  //           });
		// });


	};

});