define(function(require, exports, module) {

	exports.run = function(options) {

		$('.delete-btn').click(function() {
			if (prompt('确定删除该课程，请输入：DELETE') != 'DELETE') {
				return;
			}
			$.post($(this).data('url'), function() {
				window.location.reload();
			});
		});



		$('.open-btn').click(function() {
			
			$.post($(this).data('url'), function() {
				window.location.reload();
			});
		});

		$('.close-btn').click(function() {

			$.post($(this).data('url'), function() {
				window.location.reload();
			});
		});


	};

});
