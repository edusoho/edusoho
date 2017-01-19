define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var imageCrop = new ImageCrop({
            element: "#course-picture-crop",
            group: "course",
            cropedWidth: 480,
            cropedHeight: 270
        });

        imageCrop.on("afterCrop", function(response){
            var url = $("#upload-picture-btn").data("url");
            $.post(url, {images: response}, function(){
                document.location.href=$("#upload-picture-btn").data("gotoUrl");
            });
        });

        $("#upload-picture-btn").click(function(e){
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