define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		$('body').on('click', 'button.delete-btn', function() {
			if (!confirm('确认要删除标签吗？')) return false;
			var $btn = $(this);
			$.post($btn.data('url'), function(response) {
				if (response.status == 'ok') {
					$('#' + $btn.data('target')).remove();
					Notify.success('删除成功!');
				} else {
					alert('服务器错误!');
				}
			}, 'json');
		});

		var $table = $('#tag-table');

		$table.on('click','.tag_sort',function(){
			currentSort = parseInt($(this).text());
			
			$(this).replaceWith('<input type="text" class="tag_sort_input width-input-mini" value="'+ currentSort +'" />');
			$('.tag_sort_input').focus();
		})
		.on('blur','.tag_sort_input',function(){
			var updateSort = $(this).val();
			
			if (!/^[1-9][0-9]*$/.test(updateSort) || isNaN(updateSort)) {
				
				$(this).siblings('.text-danger').html('排序号必须为正整数');
				$(this).siblings('.text-danger').show();

				return false;
			} else {
				$(this).siblings('.text-danger').hide();
			}

			var update_url = $(this).closest('tr').find('.btn-sm').data('url');
			var tag_name = $(this).closest('tr').find('.tag_name').html();

			if (updateSort != currentSort) {
				$.post(update_url, {name:tag_name, sort:updateSort},function(html){
					$html = $(html);
					if ($table.find( '#' +  $html.attr('id')).length > 0) {
	                    $('#' + $html.attr('id')).replaceWith($html);
	                    Notify.success('排序号更新成功！');
	                }
				})
			} else {
				$(this).replaceWith('<a href="javascript:;" class="tag_sort">'+ currentSort +'</a>');
			}
			
		})

	};

});