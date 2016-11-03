
import notify from 'common/notify';

$('.delete-task').on('click', function(evt){
	console.log('delete task : ', arguments);
	if (!confirm(Translator.trans('是否确定删除任务？')))
		return;

	$.post($(evt.target).data('url'), function(data) {
		if (data.success) {
			notify('success', data.message);
			// $.notify('success', data.message);
			location.reload();
		} else{
			notify('danger', data.message);
			// $.notify('danger', data.message);
		}
	});
});