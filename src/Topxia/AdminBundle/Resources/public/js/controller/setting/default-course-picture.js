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
            var url = $("#default-course-picture-btn").data("gotoUrl");
            Notify.success('上传成功！', 1);
            document.location.href = url;
        });
        
        var $systemCoursePictureClass = $('#system-course-picture-class');

        if ($('[name=coursePicture]:checked').val() == 0) {
            $('#course-picture-class').hide();
        }
        if ($('[name=coursePicture]:checked').val() == 1) {
            $systemCoursePictureClass.hide();
        }

        $("[name=coursePicture]").on("click",function(){
            if($("[name=coursePicture]:checked").val()==0){
                $systemCoursePictureClass.show();
                $('#course-picture-class').hide();
            }
            if($("[name=coursePicture]:checked").val()==1){
                $systemCoursePictureClass.hide();
                $('#course-picture-class').show();
                defaultCoursePicUploader.enable();
            }
        });
        var $defaultCoursePicture = $("[name=defaultCoursePicture]");
        $("[name=coursePicture]").change(function(){
            $defaultCoursePicture.val($("[name=coursePicture]:checked").val());
        });

    };

});