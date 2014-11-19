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

			$('input[name="bannerUrl1"]').val( location.hostname + "/course/" + $(this).data("courseId"));
			$('div[name="bannerClassName1"]').html($(this).children('div').children('span').html())
			$modal.modal('hide');return;
			

			var $courseIds = $('input[name="courseIds"]');
			if($courseIds.val().split(",").length>3){
				Notify.danger('每周精品栏目只能设置三门课程！')
				return;
			}
			if($courseIds.val().indexOf($(this).data("courseId"))>-1){
				Notify.danger('每周精品栏目中已经0000000000存在此门课程！');
				return;
			}
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