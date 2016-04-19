define(function(require, exports, module) {

  
	var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var $table=$('#classroom-table');

		$table.on('click','.close-classroom,.open-classroom,.cancel-recommend-classroom',function(){
			var $trigger = $(this);
		if (!confirm(Translator.trans('%title%吗？',{title:$trigger.attr('title')}))) {
				return ;
			}
		$.post($(this).data('url'), function(html){
                Notify.success(Translator.trans('%title%成功！',{title:$trigger.attr('title')}));
                 var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger(Translator.trans('%title%失败',{title:$trigger.attr('title')}));
            });

		});


        $('.delete-classroom').on('click', function(){
            if (!confirm(Translator.trans('真的要删除该班级吗？'))) {
                return ;
            }
            $.post($(this).data('url'), function(){
                Notify.success(Translator.trans('删除成功！'));
                window.location.reload();
            });
        });

	}
	
});