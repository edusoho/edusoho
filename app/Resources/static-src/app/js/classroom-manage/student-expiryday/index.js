import notify from 'common/notify';

let $form = $('#expiryday-set-form');
let validator = $form.validate({
	rules: {
		deadline: {
			required: true,
			date: true,
		}
	},
	messages: {
		deadline: {
			required: '请输入有效期'
		}
	}
});

$('#student-save').click((event) => {
	if (validator.form()) {
		$(event.currentTarget).button('loadding');
		$.post($form.attr('action'), $form.serialize(), function (response) {
			if (response == true) {
				notify('success', Translator.trans('classroom_manage.student_expiryday_set_success_hint'));
			} else {
				notify('danger', Translator.trans('classroom_manage.student_expiryday_set_failed_hint'));
			}
			window.location.reload();
		});
	}
});

$('#student_deadline').datetimepicker({
	language: document.documentElement.lang,
	autoclose: true,
	format: 'yyyy-mm-dd',
	minView: 'month'
});

$('#student_deadline').datetimepicker('setStartDate', new Date);
