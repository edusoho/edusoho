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

		$form.keydown(function(e){
			if(e.keyCode==13){
			   $form.find(".btn").trigger('click');
			}
		});

		$('[role="course-list"]').find("li[role='course-item']").on('click', function(){
			var $courseIds = $('input[name="courseIds"]');
			var courseIdArray = $courseIds.val().split(",");
			if(courseIdArray.length>3){
				Notify.danger(Translator.trans('admin.course.week_excellent_courses.setting_hint'))
				return;
			}

			for (var i = courseIdArray.length - 1; i >= 0; i--) {
				if(courseIdArray[i]==$(this).data("courseId")) {
					Notify.danger(Translator.trans('admin.course.week_excellent_courses.setting_error_hint'));
					return;
				}
			};
			$courseIds.val($courseIds.val()+$(this).data("courseId")+",");
			
			if($courseIds.val().split(",").length>3){
				$('[role="add-course"]').hide();
			}

			var courseItem = $(this).clone();
			courseItem.find('[role="course-item-delete"]').on('click',function(){
	            var courseId=$(this).data("courseId");
	            var courseIds = $('input[name="courseIds"]');

	            $(this).parents('li[role="course-item"]').remove();
	            courseIds.val(courseIds.val().replace(courseId+',', ''));

	            if($courseIds.val().split(",").length<=3){
					$('[role="add-course"]').show();
				}
	        }).show();

			$('[role="add-course"]').before(courseItem);
			$modal.modal('hide');
		})
	}
});