define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {

        function afterCrops(crops) {
            crops.on("afterCrop", function(response) {
                var url = $("#upload-avatar-btn").data("url");
                $.post(url, {
                    images: response
                }, function() {
                    Notify.success(Translator.trans('admin.user.update_avatar_success_hint'), 1);
                    $('#modal').load($("#upload-avatar-btn").data("gotoUrl"));
                });
            });
    
            $("#upload-avatar-btn").click(function(e) {
                e.stopPropagation();
    
                crops.crop({
                    imgs: {
                        large: [$("#upload-avatar-btn").data("largeWidth"), $("#upload-avatar-btn").data("largeHeight")],
                        medium: [$("#upload-avatar-btn").data("largeWidth"), $("#upload-avatar-btn").data("largeHeight")],
                        small: [$("#upload-avatar-btn").data("largeWidth"), $("#upload-avatar-btn").data("largeHeight")]
                    }
                });
    
            })
    
        };

        //创建一个副本
        var imagecopy = $('#avatar-crop').clone();
        var $form = $("#avatar-crop-form"),
            $picture = $("#avatar-crop");

        var imageCrop = new ImageCrop({
            element: "#avatar-crop",
            group: "user",
            cropedWidth: 200,
            cropedHeight: 200
        });
        $('#modal #avatar-crop').on("load", function() {
            imageCrop.get('img').destroy();
            var dom = $('#modal .controls').get(0);
            $(dom).prepend(imagecopy);
            var newimageCrop = new ImageCrop({
                element: "#avatar-crop",
                group: "user",
                cropedWidth: 200,
                cropedHeight: 200
            });
            afterCrops(newimageCrop)
        });
        afterCrops(imageCrop)
        $('.go-back').click(function() {
            history.go(-1);
        });
    };
});