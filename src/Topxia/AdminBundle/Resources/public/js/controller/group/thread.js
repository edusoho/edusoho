define(function(require, exports, module) {


	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		var $table = $('#thread-table');
		require('../../util/batch-select')($('#thread-form'));

		$('#thread-delete-btn').on('click', function() {
			if ($table.find(":checkbox:checked").length < 1) {
				alert(Translator.trans('请选择要删除的话题！'));
				return false;
			}
			if (!confirm(Translator.trans('确定要删除话题吗？'))) {
				return false;
			}

			$.post($('#batchDeleteThread').attr('value'), $("#thread-form").serialize(), function(status) {
				window.location.reload();
			});

		});


		$table.on('click', '.delete-thread,.close-thread,.open-thread', function() {
			var $trigger = $(this);
			if (!confirm(Translator.trans('%trigger%吗？',{trigger:$(this).attr('title')}))) {
				return;
			}

			$.post($(this).data('url'), function(html) {
				if (html == "success") {
					Notify.success(Translator.trans('%trigger%成功！',{trigger:$trigger.attr('title')}));
					setTimeout(function() {
						window.location.reload();
					}, 1500);
				}
				Notify.success(Translator.trans('%trigger%成功！',{trigger:$trigger.attr('title')}));
				var $tr = $(html);
				$('#' + $tr.attr('id')).replaceWith(html);
			}).error(function() {
				Notify.danger(Translator.trans('%trigger%失败',{trigger:$trigger.attr('title')}));
			});

		})

		$table.on('click', ".promoted-label", function() {

			var $self = $(this);
			var postUrl = $self.data('url');
			
			$.post(postUrl, function(html) {
				var $tr = $(html);
				$('#' + $tr.attr('id')).replaceWith(html);
			});

		});

	}

});