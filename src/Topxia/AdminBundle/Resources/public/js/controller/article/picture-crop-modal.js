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
            cropedWidth = 216,
            cropedHeight = 120,
            ratio = cropedWidth / cropedHeight,
            selectWidth = 216 * (naturalWidth/scaledWidth),
            selectHeight = 120 * (naturalHeight/scaledHeight);

        $picture.Jcrop({
            trueSize: [naturalWidth, naturalHeight],
            setSelect: [0, 0, selectWidth, selectHeight],
            aspectRatio: ratio,
            onChange: function() {
                $('.jcrop-keymgr').width(0);
            },
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
                    $("#article-thumb-container").html("<img src='/files/"+fileUrl+"'>")
                    $("#article-thumb-remove").attr('style','display:block');
                    $('#modal').load($('#upload-picture-crop-btn').data('goto'));
                }
            });

        });

    };
  
});