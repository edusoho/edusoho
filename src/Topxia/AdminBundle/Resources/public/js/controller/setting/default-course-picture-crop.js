define(function(require, exports, module) {
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var imageCrop = new ImageCrop({
            element: "#default-course-picture-crop",
            cropedWidth: 480,
            cropedHeight: 270
        });

        imageCrop.on("afterCrop", function(response){
            var url = $("#upload-course-picture-btn").data("url");
            $.post(url, {images: response}, function(){
                history.go(-1);
            });
        });

        $("#upload-course-picture-btn").click(function(e){
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    large: [480, 270],
                    middle: [304, 171],
                    small: [96, 54],
                }
            });

        })

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});