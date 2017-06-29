define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var imageCrop = new ImageCrop({
            element: "#default-avatar-crop",
            group: 'system',
            cropedWidth: 270,
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
                    'avatar.png': [270, 270]
                }
            });

        })

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});