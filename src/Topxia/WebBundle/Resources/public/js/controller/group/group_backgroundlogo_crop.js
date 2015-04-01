define(function(require, exports, module) {
    var ImageCrop = require('edusoho.imagecrop');
    
    exports.run = function() {

        var imageCrop = new ImageCrop({
            element: "#logo-crop",
            group: 'group',
            cropedWidth: 1140,
            cropedHeight: 150
        });

        imageCrop.on("afterCrop", function(response){
            var url = $("#upload-picture-btn").data("url");
            $.post(url, {images: response}, function(){
                history.go(-1);
            });
        });

        $("#upload-picture-btn").click(function(e){
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    backgroundLogo: [1140,150]
                }
            });

        })

    };
  
});