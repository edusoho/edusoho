define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		$('.method-form-group').on('change',function(){
			if ($('.title-form-group').hasClass('hide')){
				$('.tagIds-form-group').addClass('hide');
				$('.title-form-group').removeClass('hide');
			} else {
				$('.tagIds-form-group').removeClass('hide');
				$('.title-form-group').addClass('hide');
			}
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
		});

		$('[data-role=batch-delete]').click(function(){

            var ids = [];
            $('[data-role=single-select]:checked').each(function(index,item) {
                ids.push($(item).data('coursewareId'));
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何课件');
                return ;
            }

            if (!confirm('您真的要删除选中的课件吗？')) {
                return ;
            }
			var $btn = $(this);
			$.post($btn.data('url'),{ids:ids},function(){
                Notify.success('删除成功！');
                window.location.reload();
			}).error(function(){
                Notify.danger('删除失败！');
            });
		});
	};

});