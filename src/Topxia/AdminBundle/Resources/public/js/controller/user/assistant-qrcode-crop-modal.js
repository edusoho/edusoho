define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {
        //创建一个副本
        var imagecopy = $('#assistant-qrcode-crop').clone();
        var $form = $("#assistant-qrcode-crop-form"),
            $picture = $("#assistant-qrcode-crop");

        var imageCrop = new ImageCrop({
            element: "#assistant-qrcode-crop",
            group: "user",
            cropedWidth: 200,
            cropedHeight: 200
        });
        $('#modal #assistant-qrcode-crop').on("load", function() {
            imageCrop.get('img').destroy();
            var dom = $('#modal .controls').get(0);
            $(dom).prepend(imagecopy);
            var newimageCrop = new ImageCrop({
                element: "#assistant-qrcode-crop",
                group: "user",
                cropedWidth: 200,
                cropedHeight: 200
            });
            newimageCrop.on("afterCrop", function(response) {
                var url = $("#upload-assistant-qrcode-btn").data("url");
                $.post(url, {
                    images: response
                }, function() {
                    Notify.success('admin.user.update_avatar_success_hint', 1);
                    $('#modal').load($("#upload-assistant-qrcode-btn").data("gotoUrl"));
                });
            });
            $("#upload-assistant-qrcode-btn").click(function(e) {
                e.stopPropagation();

                newimageCrop.crop({
                    imgs: {
                        large: [200, 200]
                    }
                });

            })
        });
        imageCrop.on("afterCrop", function(response) {
            var url = $("#upload-assistant-qrcode-btn").data("url");
            $.post(url, {
                images: response
            }, function() {
                Notify.success(Translator.trans('admin.user.update_assistant_qrcode_success_hint'), 1);
                $('#modal').load($("#upload-assistant-qrcode-btn").data("gotoUrl"));
            });
        });

        $("#upload-assistant-qrcode-btn").click(function(e) {
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    large: [200, 200]
                }
            });

        })

        $('.go-back').click(function() {
            history.go(-1);
        });
    };

});