define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('edusoho.imagecrop');

    exports.run = function() {
        //创建一个副本
        var imagecopy = $('#live-logo-crop').clone();
        var $form = $("#logo-crop-form"),
            $picture = $("#live-logo-crop"),
            $formBtn = $('#upload-avatar-btn'),
            type = $formBtn.data('type');

        var width = 280,
            height = type == 'app' ? 40 : 60;

        var imageCrop = new ImageCrop({
            element: "#live-logo-crop",
            group: 'system',
            cropedWidth: width,
            cropedHeight: height
        });
        
        imageCrop.on('afterCrop', function(response) {
            var url = $formBtn.data('url');
            $.post(url, {images: response})
            .success(function(result){
                $('#modal').modal('hide');
                var $uploadBtn = $('#'+type+'-logo-upload');

                $uploadBtn.siblings('.logo-container-js').html('<img src="' + result.url + '">');
                $uploadBtn.siblings('.logo-remove-btn-js').show();

                Notify.success(Translator.trans('上传LOGO成功！'), 1);
                
            })
            .error(function(){
                Notify.danger(Translator.trans('上传LOGO失败！'), 1);
            })
        });

        $formBtn.click(function(e) {
            e.stopPropagation();

            imageCrop.crop({
                imgs: {
                    large: [width, height],
                }
            });

        })

        $('.go-back').click(function() {
            history.go(-1);
        });
    };

});