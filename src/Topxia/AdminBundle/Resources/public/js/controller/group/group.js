define(function(require, exports, module) {

  
	var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var $table=$('#group-table');

		$table.on('click','.close-group,.open-group',function(){
			var $trigger = $(this);
		if (!confirm($trigger.attr('title') + '吗？')) {
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

        $table.on('click','.transfer-group',function(){
            $('#myModal').modal('show');
            $('#transfer-group-form').attr('action',$(this).data('url'));

        });

        var validator = new Validator({
            element: '#transfer-group-form',
            autoSubmit: false,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $.post($("#transfer-group-form").attr('action'),$("#transfer-group-form").serialize(), function(){
        
                    window.location.reload();
                })
            }
        });

        validator.addItem({
            element: '[name="user[nickname]"]',
            required: true,
            rule: 'remote'
        });

	}
	
});