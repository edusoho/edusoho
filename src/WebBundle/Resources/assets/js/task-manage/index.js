import notify from 'common/notify';

$('body').on('click','.delete-task',function(evt){
	console.log('delete task : ', arguments);
	if (!confirm(Translator.trans('是否确定删除任务？')))
		return;

	$.post($(evt.target).data('url'), function(data) {
		console.log(data);
		if (data.success) {
			notify('success', "删除成功");
			location.reload();
		} else{
			notify('danger', "删除失败");
		}
	});
});