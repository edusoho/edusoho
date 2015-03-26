define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('../widget/image-crop');

    exports.run = function() {

        var $form = $("#avatar-crop-form"),
            $picture = $("#avatar-crop");

        var imageCrop = new ImageCrop({
            element: "#avatar-crop",
            cropedWidth: 200,
            cropedHeight: 200,
        });

        imageCrop.on("select", function(c){
            $form.find("[name=x]").val(c.x);
            $form.find("[name=y]").val(c.y);
            $form.find("[name=width]").val(c.w);
            $form.find("[name=height]").val(c.h);
        });

        $("#upload-avatar-btn").click(function(e){
            e.stopPropagation();

            var cropImgUrl = $(this).data("cropImgUrl");
            var postData = {
                x: $form.find("[name=x]").val(),
                y: $form.find("[name=y]").val(),
                width: $form.find("[name=width]").val(),
                height: $form.find("[name=height]").val(),
                fileId: $form.find("[name=fileId]").val(),
                imgs: {
                    large: [200, 200],
                    medium: [120, 120],
                    small: [48, 48],
                }
            };
            $.post(cropImgUrl, postData ,function(response){
                var url = $("#upload-avatar-btn").data("url");
                $.post(url, {images: response}, function(){
                    history.go(-1);
                });
            })
        })

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});