define(function(require, exports, module) {
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var $form = $("#avatar-crop-form"),
            $picture = $("#avatar-crop");

        var imageCrop = new ImageCrop({
            element: "#logo-crop",
            group: 'group',
            cropedWidth: 200,
            cropedHeight: 200
        });

        imageCrop.on("afterCrop", function(response){
            var url = $("#upload-picture-btn").data("url");
            $.post(url, {images: response}, function(){
                document.location.href=$("#upload-picture-btn").data("reloadUrl");
            });
        });

        $("#upload-picture-btn").click(function(e){
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    logo: [200, 200]
                }
            });

        })

    };
  
});