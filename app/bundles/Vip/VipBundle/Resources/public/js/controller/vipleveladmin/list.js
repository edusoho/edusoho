define(function(require, exports, module) {

	require('jquery.sortable');
	var Notify = require('common/bootstrap-notify');
	var $table = $('#memberlevel-table');

	exports.run = function() {

		var $list = $("#memberlevel-list").sortable({
			distance: 10,
			handle: 'span.glyphicon-move',
			containerSelector: "tbody",
			itemSelector: "tr",
			placeholder: '<tr class="placeholder"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>',
		    onDrop: function (item, container, _super) {

		        _super(item, container);
		        var data = $list.sortable("serialize").get();
		        $.post($list.data('sortUrl'), {ids:data});
		    },
		    serialize: function(parent, children, isContainer) {
		        return isContainer ? children : parent.attr('id');
		    }
		});

		$table.on('click', 'a.delete-memberlevel', function() {
			if (!confirm('确认要删除此会员类型？')) return false;
			var $btn = $(this);

			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function(response){
				if (response == true) {
					$tr.remove();
					Notify.success('删除成功!');
				} else {
					Notify.warning('删除失败!');
				}
			}, 'json');

		});

		$table.on('click', 'a.on-memberlevel', function() {
			if (!confirm('确认要开启加入会员？')) return false;

			$.post($(this).data('url'), function(response){
				if (response == true) {
					Notify.success('开启成功!');
					window.location.reload();
				} else {
					Notify.warning('开启失败!');
				}
			}, 'json');

		});

		$table.on('click', 'a.off-memberlevel', function() {
			if (!confirm('确认要关闭加入会员？')) return false;

			$.post($(this).data('url'), function(response){
				if (response == true) {
					Notify.success('关闭成功!');
					window.location.reload();
				} else {
					Notify.warning('关闭失败!');
				}
			}, 'json');

		});

	};

});