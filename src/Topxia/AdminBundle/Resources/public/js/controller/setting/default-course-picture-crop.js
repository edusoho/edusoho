define(function(require, exports, module) {
    var ImageCrop = require('edusoho.imagecrop');

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

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});