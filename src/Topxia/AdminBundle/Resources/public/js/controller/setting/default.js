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
            failSilently: true,
            rule: 'maxsize_image',
            errormessageRequired: '请选择要上传的默认课程图片文件'
        });

        var $defaultAvatar = $("[name=defaultAvatar]");
        $("[name=avatar]").change(function(){
            $defaultAvatar.val($("[name=avatar]:checked").val());
        });

        if ($('[name=avatar]:checked').val() == 0)$('#avatar-class').hide();
        if ($('[name=avatar]:checked').val() == 1)$('#system-avatar-class').hide();

        $("[name=avatar]").on("click",function(){
            if($("[name=avatar]:checked").val()==0){
                $('#system-avatar-class').show();
                $('#avatar-class').hide();
            }
            if($("[name=avatar]:checked").val()==1){
                $('#system-avatar-class').hide();
                $('#avatar-class').show();
            }
        });

        if ($('[name=coursePicture]:checked').val() == 0)$('#course-picture-class').hide();
        if ($('[name=coursePicture]:checked').val() == 1)$('#system-course-picture-class').hide();

        $("[name=coursePicture]").on("click",function(){
            if($("[name=coursePicture]:checked").val()==0){
                $('#system-course-picture-class').show();
                $('#course-picture-class').hide();
            }
            if($("[name=coursePicture]:checked").val()==1){
                $('#system-course-picture-class').hide();
                $('#course-picture-class').show();
            }
        });

        var $defaultCoursePicture = $("[name=defaultCoursePicture]");
        $("[name=coursePicture]").change(function(){
            $defaultCoursePicture.val($("[name=coursePicture]:checked").val());
        });
    };

});