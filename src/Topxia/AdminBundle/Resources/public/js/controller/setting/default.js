define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $avatarForm = $("#avatar-form");

        avatarValidator = new Validator({
            element: $avatarForm
        })

        avatarValidator.addItem({
            element: '#avatar-field',
            required: true,
            rule: 'maxsize_image',
            errormessageRequired: '请选择要上传的默认头像文件'
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

        if ($('#system-course-picture-class').length > 0) {
            
            var $coursePictureForm = $("#course-picture-form");
            coursePictureValidator = new Validator({
                element: $coursePictureForm
            });

            coursePictureValidator.addItem({
                element: '#course-picture-field',
                required: true,
                rule: 'maxsize_image',
                errormessageRequired: '请选择要上传的默认课程图片文件'
            });
            
            var $systemCoursePictureClass = $('#system-course-picture-class');

            if ($('[name=coursePicture]:checked').val() == 0)$('#course-picture-class').hide();
            if ($('[name=coursePicture]:checked').val() == 1)$systemCoursePictureClass.hide();

            $("[name=coursePicture]").on("click",function(){
                if($("[name=coursePicture]:checked").val()==0){
                    $systemCoursePictureClass.show();
                    $('#course-picture-class').hide();
                }
                if($("[name=coursePicture]:checked").val()==1){
                    $systemCoursePictureClass.hide();
                    $('#course-picture-class').show();
                }
            });
            var $defaultCoursePicture = $("[name=defaultCoursePicture]");
            $("[name=coursePicture]").change(function(){
                $defaultCoursePicture.val($("[name=coursePicture]:checked").val());
            });
        };


        var validator = new Validator({
            element: '#cloud-setting-form'
        });

        validator.addItem({
            element: '[name="user_name"]',
            required: true
        });

        validator.addItem({
            element: '[name="chapter_name"]',
            required: true
        });

        validator.addItem({
            element: '[name="part_name"]',
            required: true
        });

    };

});