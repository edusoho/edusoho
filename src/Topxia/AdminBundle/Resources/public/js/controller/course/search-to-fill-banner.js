define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {
		
		var $form = $('form[role="search-form"]');
		var $modal = $form.parents('.modal');

		$form.find(".btn").on('click', function(){
			$.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            })
		});

		$form.keydown(function(e){
			if(e.keyCode==13){
			   $form.find(".btn").trigger('click');
			}
		});

		$('[role="course-list"]').find("li[role='course-item']").on('click', function(){
			var element = $('[data-status="active"]').parent();
			$(element.find('input')[0]).val($(this).data("courseId"));
			
            var courseItem = $(this).clone();
            $(element.find("[role='bannerCourse']")[0]).html(courseItem);
			$modal.modal('hide');return;
			
		})
	}
});