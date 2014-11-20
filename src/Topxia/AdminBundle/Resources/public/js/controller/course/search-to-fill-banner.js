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
			$('input[name="bannerUrl'+who_is_clicked+'"]').val( location.hostname + "/course/" + $(this).data("courseId"));
			$('input[name="bannerJumpToCourseId'+who_is_clicked+'"]').val($(this).data("courseId"));
						
            $("#bannerCourseShow"+who_is_clicked).show();
            $("#bannerCourseChooseButton"+who_is_clicked).show();

            var courseItem = $(this).clone();
            $("[role='bannerCourse"+who_is_clicked+"']").html(courseItem);

			$modal.modal('hide');return;
			
		})
	}
});