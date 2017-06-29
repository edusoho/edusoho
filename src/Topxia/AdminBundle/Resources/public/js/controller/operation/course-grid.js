define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');
    require('es-ckeditor');

    exports.run = function() {

        $('li[role="course-item"]').find("[role='course-item-delete']").on('click',function(){
            var courseId=$(this).data("courseId");
            var courseIds = $('input[name="courseIds"]');

            $(this).parents('li[role="course-item"]').remove();
            courseIds.val(courseIds.val().replace(courseId+',', ''));
            if(courseIds.val().split(",").length<=3){
                $('[role="add-course"]').show();
            }
        });

    };

});