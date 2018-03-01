import SmsSender from 'app/common/widget/sms-sender';
import notify from 'common/notify';

let $form = $('#bind-mobile-form');
let smsSend = '.js-sms-send';
let $smsCode = $(smsSend);

$form.validate({
	currentDom: '#submit-btn',
	ajax: true,
	rules: {
		password: {
			required: true,
			es_remote: {
				type: 'post'
			},
		},
		mobile: {
			required: true,
			phone: true,
			es_remote: {
				type: 'get',
				callback: (bool) => {
					if (bool) {
						$smsCode.removeAttr('disabled');
					} else {
						$smsCode.attr('disabled', true);
					}
				}
			},
		},
		sms_code: {
			required: true,
			unsigned_integer: true,
			es_remote: {
				type: 'get',
			},
		},
	},
	messages: {
		sms_code: {
			required: Translator.trans('site.captcha_code.required')
		}
	},
	submitSuccess(data) {
		notify('success', Translator.trans(data.message));
		$('.modal').modal('hide');
		window.location.reload();
	},
	submitError(data) {
		notify('danger',  Translator.trans(data.responseJSON.message));
	}
});

$smsCode.on('click', function() {
	new SmsSender({
		element: smsSend,
		url: $smsCode.data('url'),
		smsType: 'sms_bind',
	});
});
