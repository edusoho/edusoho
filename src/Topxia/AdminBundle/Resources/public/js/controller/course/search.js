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
			$('div[role="course-item-container"]').append($(this).clone());
			$modal.modal('hide');
		})
	}
});