define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');
    var ImageCrop = require('../widget/image-crop');

    exports.run = function() {
      	require('./header').run();

        var $form = $("#course-picture-crop-form");

        new ImageCrop({
            element: "#course-picture-crop",
            x: $form.find("[name=x]"),
            y: $form.find("[name=y]"),
            width: $form.find("[name=width]"),
            height: $form.find("[name=height]")
        });

        $('.go-back').click(function(){
            history.go(-1);
        });
    };
  
});