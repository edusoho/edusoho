define(function(require, exports, module) {

  
	var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var $table=$('#group-table');

		$table.on('click','.close-group,.open-group',function(){
			var $trigger = $(this);
		if (!confirm(Translator.trans('admin.group.operating_hint',{trigger:$trigger.attr('title')}))) {
				return ;
			}
		$.post($(this).data('url'), function(html){
                Notify.success(Translator.trans('admin.group.operating_success_hint',{trigger:$trigger.attr('title')}));
                 var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger(Translator.trans('admin.group.operating_fail_hint',{trigger:$trigger.attr('title')}));
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