define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
    var ClassChooser = require('../class/class-chooser');

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




        //调用
        var classChooser = new ClassChooser({
            element:'#class_show',
            modalTarget:$('#modal'),
            url:'/admin/class/list'
        });
        
        classChooser.on('choosed',function(id,name){
            $form.find('#class_hidden').val(id);
            $form.find('#class_show').val(name);
        });	       

    };

});
