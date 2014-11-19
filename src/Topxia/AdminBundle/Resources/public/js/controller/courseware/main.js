define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		$('#method').on('change',function(){
			$('#tags').toggle();
			$('#title').toggle();
		});

		$('.delete-courseware-btn').click(function(){

            if (!confirm('您真的要删除该课件吗？')) {
                return ;
            }

			var $btn = $(this);
			$.post($btn.data('url'),function(){
                Notify.success('删除成功！');
                window.location.reload();
			}).error(function(){
                Notify.danger('删除失败！');
            });
		});

		$('[data-role=batch-select]').click(function(){
            if ($(this).is(":checked") == true){
                $('[data-role=single-select]').prop('checked', true);
            } else {
               $('[data-role=single-select]').prop('checked', false);
            }
		})
	};

});