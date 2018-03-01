import notify from 'common/notify';

$('.course-publish-btn').click(function () {
	if (!confirm(Translator.trans('open_course.publish_hint'))) {
		return;
	}
	$.post($(this).data('url'), function (response) {
		if (!response['result']) {
			notify('danger',response['message']);
		} else {
			window.location.reload();
		}
	});
});

$('.js-exit-course').on('click', function () {
	var self = $(this);
	$.post($(this).data('url'), function () {
		window.location.href = self.data('go');
	});
});
