define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $form = $('#approve-form');
		$('button[type=submit]').click(function() {

			var submitButton = $(this);
			var status = submitButton.data('status');

			if (status == 'fail'){
				var ret=confirm(Translator.trans('是否确认审核失败?'));
				if (!ret) {
					return false;
				}
			}
			
			if (status == 'success'){
				var ret=confirm(Translator.trans('是否确认审核通过?'));
				if (!ret) {
					return false;
				}
			}

			if (status == 'fail' && $('#note').val() == '') {
				Notify.danger(Translator.trans('请输入驳回原因！'));
				return false;
			}

			if($("#note").val().length > 100){
				Notify.danger(Translator.trans('不好意思，备注太长，请限制在100个字以内!'));
				return false;
			}

			$('#form_status').val(status);
			$('.user-approve-btn').button('submiting').addClass('disabled');
			$.post($form.attr('action'), $form.serialize(), function(response){
				var originText = submitButton.text();
				submitButton.text(Translator.trans('提交中...'));
				$('button').attr('disabled', 'disabled');

				if (response.status == 'error') {
					Notify.danger(response.error.message);
					submitButton.text(originText);
					$('button').attr('disabled', false);
				} else {
					window.location.reload();
				}

			}, 'json');

			return false;

		});

	};

});