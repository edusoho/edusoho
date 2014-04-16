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
                    $("#article-thumb-container").css('display','block');
                    response =  eval("("+response+")");
                    var fileUrl = response.fileOriginalPath+"/"+response.fileOriginalName;
                    var fileUrlOriginal = response.fileOriginalPath+"/"+response.fileOriginalNameNew;
                    $('#article-thumb').val(fileUrl);
                    $('#article-originalThumb').val(fileUrlOriginal);
                    $('#article-thumb-preview').attr('src',fileUrl);
                    $('#modal').load($('#upload-picture-crop-btn').data('goto'));
                }
            });

        });

    };
  
});