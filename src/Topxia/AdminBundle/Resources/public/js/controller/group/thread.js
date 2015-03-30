define(function(require, exports, module) {


	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		var $table = $('#thread-table');
		require('../../util/batch-select')($('#thread-table'));

		$('#deleteThread').on('click', function() {
			if ($(":checkbox:checked").length < 1) {
				alert("请选择要删除的话题！");
				return false;
			}
			if (!confirm('确定要删除话题吗？')) {
				return false;
			}

			$.post($('#batchDeleteThread').attr('value'), $("#thread-form").serialize(), function(status) {
				window.location.reload();
			});

		});


		$table.on('click', '.delete-thread,.close-thread,.open-thread', function() {
			var $trigger = $(this);
			if (!confirm($(this).attr('title') + '吗？')) {
				return;
			}

			$.post($(this).data('url'), function(html) {
				if (html == "success") {
					Notify.success($trigger.attr('title') + '成功！');
					setTimeout(function() {
						window.location.reload();
					}, 1500);
				}
				Notify.success($trigger.attr('title') + '成功！');
				var $tr = $(html);
				$('#' + $tr.attr('id')).replaceWith(html);
			}).error(function() {
				Notify.danger($trigger.attr('title') + '失败');
			});

		})

		$table.on('click', ".promoted-label", function() {

			var $self = $(this);
			var span = $self.find('span');
			var spanClass = span.attr('class');
			var postUrl = "";
			var labelStatus = "";

			if (spanClass == "label label-default") {
				postUrl = $self.data('setUrl');
				labelStatus = "label label-success";

			} else {
				postUrl = $self.data('cancelUrl');
				labelStatus = "label label-default";

			}

			$.post(postUrl, function(html) {
				var $tr = $(html);
				$('#' + $tr.attr('id')).replaceWith(html);
			});

		});

	}

});