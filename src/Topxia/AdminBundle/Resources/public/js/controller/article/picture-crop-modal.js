define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $("#article-pic-crop-form"),
            $picture = $("#article-pic-crop");
        var $modal = $form.parents('.modal');

        var scaledWidth = $picture.attr('width'),
            scaledHeight = $picture.attr('height'),
            naturalWidth = $picture.data('naturalWidth'),
            naturalHeight = $picture.data('naturalHeight'),
            cropedWidth = 250,
            cropedHeight = 125,
            ratio = cropedWidth / cropedHeight,
            selectWidth = 250 * (naturalWidth/scaledWidth),
            selectHeight = 125 * (naturalHeight/scaledHeight);

        $picture.Jcrop({
            trueSize: [naturalWidth, naturalHeight],
            setSelect: [0, 0, selectWidth, selectHeight],
            aspectRatio: ratio,
            onSelect: function(c) {
                $form.find('[name=x]').val(c.x);
                $form.find('[name=y]').val(c.y);
                $form.find('[name=width]').val(c.w);
                $form.find('[name=height]').val(c.h);
            }
        });

        $("#upload-picture-crop-btn").click(function() {

            $form.ajaxSubmit({
                clearForm: true,
                success: function(response){
                    $modal.modal('hide');
                    response =  eval("("+response+")");
                    var file_url = response.file_original_path+"/"+response.file_original_name;
                    var file_url_original = response.file_original_path+"/"+response.file_original_name_new;
                    $('#article-thumb').val(file_url);
                    $('#article-originalThumb').val(file_url_original);
                    $('#article-thumb-preview').attr('src',file_url);
                    $('#modal').load($('#upload-picture-crop-btn').data('goto'));
                }
            });

        });

    };
  
});