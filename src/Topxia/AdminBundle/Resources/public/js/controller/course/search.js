define(function(require, exports, module) {
	exports.run = function() {
		
		var $form = $('form[role="search-form"]');
		var $modal = $form.parents('.modal');

		$form.find(".btn").on('click', function(){
			$.post($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            })
		});

		$("div[role='course-item']").on('click', function(){
			var $courseIds = $('input[name="courseIds"]');
			$courseIds.val($courseIds.val()+$(this).data("courseId")+",");
			
			var courseItem = $(this).clone();
			courseItem.find('a').on('click',function(){
	            var courseId=$(this).data("courseId");
	            var courseIds = $('input[name="courseIds"]');

	            $(this).parents('div[role="course-item"]').remove();
	            courseIds.val(courseIds.val().replace(courseId+',', ''));
	        }).show();

			$('div[role="course-item-container"] .row').append(courseItem);
			$modal.modal('hide');
		})
	}
});