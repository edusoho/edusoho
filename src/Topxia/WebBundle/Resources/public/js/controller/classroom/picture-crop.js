define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var imageCrop = new ImageCrop({
            element: "#classroom-picture-crop",
            group: "course",
            cropedWidth: 525,
            cropedHeight: 350
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
                    large: [525, 350],
                    middle: [345, 230],
                    small: [213, 142],
                }
            });

        })

        $('.go-back').click(function(){
            history.go(-1);
        });

    };
  
});