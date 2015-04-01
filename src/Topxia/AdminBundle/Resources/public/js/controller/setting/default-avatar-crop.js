define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {


        // var scaledWidth = $picture.attr('width'),
        //     scaledHeight = $picture.attr('height'),
        //     naturalWidth = $picture.data('naturalWidth'),
        //     naturalHeight = $picture.data('naturalHeight'),
        //     cropedWidth = 220,
        //     cropedHeight = 220,
        //     ratio = cropedWidth / cropedHeight,
        //     selectWidth = 200 * (naturalWidth/scaledWidth),
        //     selectHeight = 200 * (naturalHeight/scaledHeight);

        // $picture.Jcrop({
        //     trueSize: [naturalWidth, naturalHeight],
        //     setSelect: [0, 0, selectWidth, selectHeight],
        //     aspectRatio: ratio,
        //     onSelect: function(c) {
        //         $form.find('[name=x]').val(c.x);
        //         $form.find('[name=y]').val(c.y);
        //         $form.find('[name=width]').val(c.w);
        //         $form.find('[name=height]').val(c.h);
        //     }
        // });

        var imageCrop = new ImageCrop({
            element: "#default-avatar-crop",
            cropedWidth: 200,
            cropedHeight: 200,
        });

        imageCrop.on("afterCrop", function(response){
            var url = $("#upload-avatar-btn").data("url");
            $.post(url, {images: response}, function(){
                history.go(-1);
            });
        });

        $("#upload-avatar-btn").click(function(e){
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    large: [200, 200],
                    small: [120, 120]
                }
            });

        })

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});