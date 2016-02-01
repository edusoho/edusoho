define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        var $form = $("#avatar-crop-form"),
            $picture = $("#avatar-crop");
        var $modal = $("#modal");

        var imageCrop = new ImageCrop({
            element: "#avatar-crop",
            group: "user",
            cropedWidth: 200,
            cropedHeight: 200
        });

        imageCrop.on("afterCrop", function(response){
            var url = $("#upload-avatar-btn").data("url");
            $.post(url, {images: response}, function(data){
                $modal.modal('hide');
                $("#smallAvatar").val(data.small.uri);
                $("#mediumAvatar").val(data.medium.uri);
                $("#largeAvatar").val(data.large.uri);
                $("#user-avatar").html("<img src='"+data.large.url+"'>")
            });
        });

        $("#upload-avatar-btn").click(function(e){
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    large: [200, 200],
                    medium: [120, 120],
                    small: [48, 48]
                }
            });

        })

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});