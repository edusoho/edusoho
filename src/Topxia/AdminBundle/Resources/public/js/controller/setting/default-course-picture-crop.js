define(function(require, exports, module) {
    var ImageCrop = require('edusoho.imagecrop');

    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var imageCrop = new ImageCrop({
            element: "#default-course-picture-crop",
            group: 'system',
            cropedWidth: 480,
            cropedHeight: 270
        });

        imageCrop.on("afterCrop", function(response){
            var url = $("#upload-course-picture-btn").data("url");
            $.post(url, {images: response}, function(){
                document.location.href=$("#upload-course-picture-btn").data("gotoUrl");
            });
        });

        $("#upload-course-picture-btn").click(function(e){
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    'course.png': [480, 270]
                }
            });

        })

        var defaultCoursePicUploader = new WebUploader({
            element: '#default-course-picture-btn'
        });

        defaultCoursePicUploader.on('uploadSuccess', function(file, response ) {
            var url = $("#default-course-picture-btn").data("gotoUrl");
            Notify.success(Translator.trans('admin.setting.course.upload_default_pic_success_hint'), 1);
            document.location.href = url;
        });

    };
  
});