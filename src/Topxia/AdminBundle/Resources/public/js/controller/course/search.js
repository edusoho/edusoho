define(function(require, exports, module) {
	exports.run = function() {
		
		var $form = $('form[role="search-form"]');
		var $modal = $form.parents('.modal');

		$form.find(".btn").on('click', function(){
			$.post($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            })
		});

		$('[role="course-list"]').find("li[role='course-item']").on('click', function(){
			var $courseIds = $('input[name="courseIds"]');
			$courseIds.val($courseIds.val()+$(this).data("courseId")+",");
			
			var courseItem = $(this).clone();
			courseItem.find('[role="course-item-delete"]').on('click',function(){
	            var courseId=$(this).data("courseId");
	            var courseIds = $('input[name="courseIds"]');

	            $(this).parents('li[role="course-item"]').remove();
	            courseIds.val(courseIds.val().replace(courseId+',', ''));
	        }).show();

			$('[role="course-item-container"] ul').append(courseItem);
			$modal.modal('hide');
		})
	}
});