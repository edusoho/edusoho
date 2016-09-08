define(function(require, exports, module) {

	var Notify = require("common/bootstrap-notify");

	exports.run = function() {

		$('button.course-confirm-unlock').on(
				'click',
				function(e) {
					$(this).text("正在解锁");
					$(this).attr("disabled", "disabled");
					var button = $(this);
					$.post($(this).attr('data-url'), "", function(data) {
						if (data.success) {
							var redirect_url = $(button).attr("redirect-url")
									+ "#lesson/" + data.nextLearnLessonId;
							window.location.href = redirect_url;
						} else {
							Notify.danger(data.message);
							$(button).text("解锁");
							$(button).removeAttr("disabled");
						}
					});

				});
	};

});