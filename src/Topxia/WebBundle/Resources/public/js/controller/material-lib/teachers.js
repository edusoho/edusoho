define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	var Notify = require('common/bootstrap-notify');

	require('common/validator-rules').inject(Validator);

	exports.run = function() {

		var $modal = $('#share-materials-form').parents('.modal');

		var selectedUser = $('#s2id_target-teachers-input');

		var teacherSelect = $('#target-teachers-input');

		teacherSelect.on('change', function(data) {
			$('.help-block').hide();
		});

		var validator = new Validator({
			element : '#share-materials-form',
			autoSubmit : false,
			onFormValidated : function(error, results, $form) {
				if (error) {
					return false;
				}

				var $btn = $("#share-materials-form-submit");
				$btn.button('submiting').addClass('disabled');

				$.post($form.attr('action'), {
					targetUserIds : $("#target-teachers-input").select2("val")
				}, function(html) {
					Notify.success(Translator.trans('notify.share_success.message'));
					$modal.modal('hide');
				}).error(function() {
					Notify.danger(Translator.trans('notify.share_fail.message'));
					$btn.button('reset').removeClass('disabled');
				});
			},
		});
		// var rule =
		validator.addItem({
				element: '#target-teachers-input',
				required: true,
				rule: 'visible_character',
				display: Translator.trans('material.share.teacher_nickname_label')
		});


		addTeacher = function(id, nickname) {
			var selectedUsers = $("#target-teachers-input").select2("val");

			var error = '';

			if ($.inArray(id, selectedUsers) >= 0) {
				error = Translator.trans('site.data.already_exists');
			}

			if (error) {
				Notify.danger(error);
			} else {
				var data = $("#target-teachers-input").select2("data");

				data.push({
					id : id,
					text : nickname
				});

				$("#target-teachers-input").select2("data", data);

				$('#teacher-select').val("");
			}
		}
	};
});
