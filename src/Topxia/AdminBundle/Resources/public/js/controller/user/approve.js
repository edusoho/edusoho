define(function(require, exports, module) {

	exports.run = function() {

		var $form = $('#approve-form');
		$('button[type=submit]').click(function() {
			var submitButton = $(this);
			var status = submitButton.data('status');

			if (status == 'fail' && $('#form_note').val() == '') {
				alert('请输入审核失败理由！');
				return false;
			}
			$('#form_status').val(status);

			$.post($form.attr('action'), $form.serialize(), function(response){
				var originText = submitButton.text();
				submitButton.text('提交中...');
				$('button').attr('disabled', 'disabled');

				if (response.status == 'error') {
					alert(response.error.message);
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