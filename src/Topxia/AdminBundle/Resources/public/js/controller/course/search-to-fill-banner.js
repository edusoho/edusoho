define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {
		
		var $form = $('form[role="search-form"]');
		var $modal = $form.parents('.modal');

		$form.find(".btn").on('click', function(){
			$.post($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            })
		});

		$('[role="course-list"]').find("li[role='course-item']").on('click', function(){
			var who_is_clicked = $("#who_is_clicked").html();
			$('input[name="bannerUrl'+who_is_clicked+'"]').val( "http://"+location.hostname + "/course/" + $(this).data("courseId"));
			$('div[name="bannerClassName'+who_is_clicked+'"]').html($(this).children('div').children('span').html())
		    $("#bannerUrl"+who_is_clicked).show();
            $("#bannerClassNameLabel"+who_is_clicked).show();
            $("#bannerClassName"+who_is_clicked).show();
            $("#bannerCourseChooseButton"+who_is_clicked).show();
			$modal.modal('hide');return;
			
		})
	}
});