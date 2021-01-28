define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {
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
            newimageCrop.on("afterCrop", function(response) {
                var url = $("#upload-avatar-btn").data("url");
                $.post(url, {
                    images: response
                }, function() {
                    Notify.success('admin.user.update_avatar_success_hint', 1);
                    $('#modal').load($("#upload-avatar-btn").data("gotoUrl"));
                });
            });
            $("#upload-avatar-btn").click(function(e) {
                e.stopPropagation();

                newimageCrop.crop({
                    imgs: {
                        large: [200, 200],
                        medium: [120, 120],
                        small: [48, 48]
                    }
                });

            })
        });
        imageCrop.on("afterCrop", function(response) {
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

            imageCrop.crop({
                imgs: {
                    large: [200, 200],
                    medium: [120, 120],
                    small: [48, 48]
                }
            });

        })

        $('.go-back').click(function() {
            history.go(-1);
        });
    };

});