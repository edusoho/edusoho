import notify from 'common/notify';

let $modal = $('#student-create-form').parents('.modal');
let $form = $('#student-create-form');
let $table = $('#course-student-list');
let $btn = $('#student-create-form-submit');
let validator = $form.validate({
	onkeyup: false,
	rules: {
		queryfield: {
			required: true,
			remote: {
				url: $('#student-nickname').data('url'),
				type: 'get',
				data: {
					'value': function () {
						return $('#student-nickname').val();
					}
				}
			}
		},
		remark: {
			maxlength: 80,
		},
		price: {
			currency: true,
		}
	},
	messages: {
		queryfield: {
			remote: Translator.trans('classroom_manage.student_create_field_required_error_hint')
		}
	}
});

$btn.click(() => {
	if (validator.form()) {
		$btn.button('submiting').addClass('disabled');
		$.post($form.attr('action'), $form.serialize(), function () {
			$modal.modal('hide');
			notify('success', Translator.trans('classroom_manage.student_create_add_success_hint'));
			window.location.reload();
		}).error(function () {
			notify('danger', Translator.trans('classroom_manage.student_create_add_failed_hint'));
			$btn.button('reset').removeClass('disabled');
		});
	}
});
