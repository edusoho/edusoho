define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('../widget/image-crop');

    exports.run = function() {
      	require('./header').run();

        var $form = $("#course-picture-crop-form");

        var imageCrop = new ImageCrop({
            element: "#course-picture-crop",
            cropedWidth: 480,
            cropedHeight: 270
        });

        imageCrop.on("select", function(c){
            $form.find("[name=x]").val(c.x);
            $form.find("[name=y]").val(c.y);
            $form.find("[name=width]").val(c.w);
            $form.find("[name=height]").val(c.h);
        });

        imageCrop.triggerSelect();

        $("#upload-picture-btn").click(function(e){
            e.stopPropagation();

            var cropImgUrl = $(this).data("cropImgUrl");
            var postData = {
                x: $form.find("[name=x]").val(),
                y: $form.find("[name=y]").val(),
                width: $form.find("[name=width]").val(),
                height: $form.find("[name=height]").val(),
                fileId: $form.find("[name=fileId]").val(),
                imgs: {
                    large: [480, 270],
                    middle: [304, 171],
                    small: [96, 54],
                }
            };
            $.post(cropImgUrl, postData ,function(response){
                var url = $("#upload-picture-btn").data("url");
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