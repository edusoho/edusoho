define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {

    if ($('#system-course-picture-class').length > 0) {

        var $coursePictureForm = $("#course-picture-form");
        coursePictureValidator = new Validator({
            element: $coursePictureForm
        });

        coursePictureValidator.addItem({
            element: '#course-picture-field',
            required: true,
            rule: 'maxsize_image',
            errormessageRequired: Translator.trans('admin.setting.course_avatar.select_default_pic.message')
        });

        var $systemCoursePictureClass = $('#system-course-picture-class');

        if ($('[name=coursePicture]:checked').val() == 0) $('#course-picture-class').hide();
        if ($('[name=coursePicture]:checked').val() == 1) $systemCoursePictureClass.hide();

        $("[name=coursePicture]").on("click", function() {
            if ($("[name=coursePicture]:checked").val() == 0) {
                $systemCoursePictureClass.show();
                $('#course-picture-class').hide();
        }
        if ($("[name=coursePicture]:checked").val() == 1) {
            $systemCoursePictureClass.hide();
            $('#course-picture-class').show();
        }
        });
        var $defaultCoursePicture = $("[name=defaultCoursePicture]");
            $("[name=coursePicture]").change(function() {
            $defaultCoursePicture.val($("[name=coursePicture]:checked").val());
        });
        };


  };


});