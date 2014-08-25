define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $avatarForm = $("#avatar-form");
        var $coursePictureForm = $("#course-picture-form");

        avatarValidator = new Validator({
            element: $avatarForm
        })

        coursePictureValidator = new Validator({
            element: $coursePictureForm
        })

        avatarValidator.addItem({
            element: '#avatar-field',
            required: true,
            rule: 'maxsize_image',
            errormessageRequired: '请选择要上传的默认头像文件'
        });

        coursePictureValidator.addItem({
            element: '#course-picture-field',
            required: true,
            rule: 'maxsize_image',
            errormessageRequired: '请选择要上传的默认课程图片文件'
        });

        var $defaultAvatar = $("[name=defaultAvatar]");
        $("[name=avatar]").change(function(){
            $defaultAvatar.val($("[name=avatar]:checked").val());
        });

        var $defaultCoursePicture = $("[name=defaultCoursePicture]");
        $("[name=coursePicture]").change(function(){
            $defaultCoursePicture.val($("[name=coursePicture]:checked").val());
        });
    };

});