define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var imageCrop = new ImageCrop({
            element: "#default-avatar-crop",
            group: 'system',
            cropedWidth: 200,
            cropedHeight: 200
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
                    'avatar.png': [200, 200]
                }
            });

        })

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});