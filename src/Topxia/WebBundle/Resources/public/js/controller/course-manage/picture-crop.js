define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
      	require('./header').run();

        var $form = $("#course-picture-crop-form"),
            $picture = $("#course-picture-crop");

        var scaledWidth = $picture.attr('width'),
            scaledHeight = $picture.attr('height'),
            naturalWidth = $picture.data('naturalWidth'),
            naturalHeight = $picture.data('naturalHeight'),
            cropedWidth = 480,
            cropedHeight = 270,
            ratio = cropedWidth / cropedHeight,
            selectWidth = 360 * (naturalWidth/scaledWidth),
            selectHeight = 202.5 * (naturalHeight/scaledHeight);

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

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});