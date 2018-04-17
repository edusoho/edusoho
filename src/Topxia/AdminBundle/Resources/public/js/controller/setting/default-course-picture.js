define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  var WebUploader = require('edusoho.webuploader');
  var Notify = require('common/bootstrap-notify');

  exports.run = function() { 
    var defaultCoursePicUploader = new WebUploader({
      element: '#default-course-picture-btn'
    });

    defaultCoursePicUploader.on('uploadSuccess', function(file, response ) {
      var url = $('#default-course-picture-btn').data('gotoUrl');
      Notify.success(Translator.trans('上传成功！'), 1);
      document.location.href = url;
    });
      
    var $systemCoursePictureClass = $('#system-course-picture-class');
    var $coursePictureClass = $('#course-picture-class');
    
    $('[name=defaultCoursePicture]').on('click',function(){
      var $this = $(this);
      if($this.val() == 0){
        $systemCoursePictureClass.show();
        $coursePictureClass.hide();
      }
      if($this.val() == 1){
        $systemCoursePictureClass.hide();
        $coursePictureClass.show();
      }
    });

  };
});