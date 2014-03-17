define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $("#message-search-form"),
            $modal = $form.parents('.modal'),
            $table = $("#course-table");

        $form.submit(function(e) {
            e.preventDefault();
            $.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            });
        });

        $table.on('click', '.choose-course', function(e){
        	var courseId = $(this).data('target');
        	var courseName = $(this).data('name');
        	$('#choose-course-input').val(courseId);
        	$('#course-display .well').html(courseName);
        	$('#course-display').show();

            $modal.modal('hide');
            Notify.success('指定课程成功');
        });
    };
})