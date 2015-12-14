define(function(require, exports, module) {

      exports.run = function() {
        var seq = parseInt($('.number:last').text()) ;
        if(!$("#course-lesson-form").data('lessonId')){
            seq  += 1;
        }
        $("#course-lesson-form").append('<input type="hidden" name="seq_number"  value="'+seq+'">');
         require('topxiawebbundle/controller/course-manage/lesson-modal').run();
      }
});
