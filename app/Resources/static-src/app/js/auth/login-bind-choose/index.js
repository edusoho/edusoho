import notify from 'common/notify';

let $form = $('#set-bind-new-form');
let validator = $form.validate({
	currentDom: '#set-bind-new-btn',
  ajax: true,
  rules: {
    nickname: {
      required: true,
      byte_minlength: 4,
      byte_maxlength: 18,
      nickname: true,
      chinese_alphanumeric: true,
      es_remote: {
        type: 'get',
      }
    },
    set_bind_emailOrMobile: {
      required: true,
      es_email: true,
      es_remote: {
        type: 'get'
      }
    }
  },
	submitSuccess(response) {
		if (!response.success) {
			$('#bind-new-form-error').html(response.message).show();
			return;
		}
		notify('success',Translator.trans('auth.login_bind_choose.login_success_hint'));
		window.location.href = response._target_path;
	},
	submitError: function (data) {
		notify('danger',Translator.trans('auth.login_bind_choose.login_failed_hint'));
	},
})


$('#set-bind-new-btn').click(() => {
	$('#set-bind-new-form').submit();
})

$('#user_terms input[type=checkbox]').on('click', function () {
  if ($(this).attr('checked')) {
    $(this).attr('checked', false);
  } else {
    $(this).attr('checked', true);
  };
});
