import { initEditor } from './editor';
import notify from 'common/notify';
import AttachmentActions from 'app/js/attachment/widget/attachment-actions';

export const initThread = () => {
	let btn = '#post-thread-btn';
	let $form = $('#post-thread-form');
	new AttachmentActions($form);

	if($('#post_content').length) {
		initEditor({
			toolbar: 'Thread',
			replace: 'post_content'
		});
	}

	let formValidator = $form.validate({
		currentDom: btn,
		ajax: true,
		rules: {
			'content': {
				required: true,
				minlength: 2,
				trim: true
			}
		},
		submitError: function (data) {
			data = data.responseText;
			data = $.parseJSON(data);
			if (data.error) {
				notify('danger', data.error.message);
			} else {
				notify('danger', Translator.trans('group.post.reply_fail_hint'));
			}
		},
		submitSuccess: function (data) {
			console.log(data);
			if (data == '/login') {
				window.location.href = url;
				return;
			}
			// @TODO优化不刷新页面
			window.location.reload();
		},
	});
	console.log(formValidator);
	$(btn).click(() => {
		formValidator.form();
	});
};

export const initThreadReplay = () => {
	let $forms = $('.thread-post-reply-form');
	$forms.each(function () {
		let $form = $(this);
		let content = $form.find('textarea').attr('name');
		let formValidator = $form.validate({
			ignore: '',
			rules: {
				[`${content}`]: {
					required: true,
					minlength: 2,
					trim: true
				}
			},
			submitHandler: function (form) {
				// @TODO优化全局的submitHandler方法，提交统一方式；
				var $replyBtn = $(form).find('.reply-btn');
				var postId = $replyBtn.attr('postId');
				var fromUserIdVal = '';
				if ($('#fromUserId').length > 0) {
					fromUserIdVal = $('#fromUserId').val();
				} else {
					if ($('#fromUserIdNosub').length > 0) {
						fromUserIdVal = $('#fromUserIdNosub').val();
					} else {
						fromUserIdVal = '';
					}
				}
				$replyBtn.button('submiting').addClass('disabled');
				console.log($(form).attr('action'));
				console.log('content=' + $(form).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal);
				$.ajax({
					url: $(form).attr('action'),
					data: 'content=' + $(form).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal,
					cache: false,
					async: false,
					type: 'POST',
					dataType: 'text',
					success: function (url) {
						if (url == '/login') {
							window.location.href = url;
							return;
						}
						// @TODO优化不刷新页面
						window.location.reload();
					},
					error: function (data) {
						data = $.parseJSON(data.responseText);
						if (data.error) {
							notify('danger',data.error.message);
						} else {
							notify('danger',Translator.trans('group.post.reply_fail_hint'));
						}
						$replyBtn.button('reset').removeClass('disabled');
					}
				});
			}
		});
		$form.find('button').click((e) => {
			formValidator.form();
		});
	});
};