define(function(require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  var WebUploader = require('edusoho.webuploader');
  var Notify = require('common/bootstrap-notify');
  // 防止切换时，上传按钮点击失效问题
  // 隐藏元素初始化上传组件时，会有问题
  var isInitUPloader = false;

  exports.run = function() { 
    var $systemCoursePictureClass = $('#system-course-picture-class');
    var $coursePictureClass = $('#course-picture-class');
    if (!$coursePictureClass.hasClass('hide')) {
      initUploader();
    }

    $('[name=defaultCoursePicture]').on('click',function(){
      var $this = $(this);
      if($this.val() == 0){
        $systemCoursePictureClass.removeClass('hide');
        $coursePictureClass.addClass('hide');
      }
      if($this.val() == 1){
        $systemCoursePictureClass.addClass('hide');
        $coursePictureClass.removeClass('hide');
        if (!isInitUPloader) {
          initUploader();
        }
      }
    });

    function initUploader() {
      var defaultCoursePicUploader = new WebUploader({
        element: '#default-course-picture-btn'
      });

      defaultCoursePicUploader.on('uploadSuccess', function(file, response ) {
        var url = $('#default-course-picture-btn').data('gotoUrl');
        Notify.success(Translator.trans('admin.setting.course.upload_default_pic_success_hint'), 1);
        document.location.href = url;
      });
      isInitUPloader = true;
    }

  };
});