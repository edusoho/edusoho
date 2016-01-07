define(function(require, exports, module) {

      exports.run = function() {
        var lessonId = $("#course-lesson-form").data('lessonId');
        if(lessonId){
        	seq = $("#lesson-"+lessonId+" .number").text();
        }else{
        	var maxNumber = $('.number:last').text();
        	seq = (maxNumber === "") ? 0 :  parseInt(maxNumber) +1;
        }
        $("#course-lesson-form").append('<input type="hidden" name="seq_number"  value="'+seq+'">');
         require('topxiawebbundle/controller/course-manage/lesson-modal').run();
      };
});
