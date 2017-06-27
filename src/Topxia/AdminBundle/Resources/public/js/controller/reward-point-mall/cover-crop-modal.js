define(function(require, exports, module) {

  var ImageCrop = require('edusoho.imagecrop');

  var ImageModalCrop = ImageCrop.extend({
    setup:function() {
      var self = this;
      var $picture = this.element;

      scaledWidth = $picture.attr('width'),
      scaledHeight = $picture.attr('height'),
      naturalWidth = $picture.data('naturalWidth'),
      naturalHeight = $picture.data('naturalHeight'),
      cropedWidth = this.get("cropedWidth"),
      cropedHeight = this.get("cropedHeight"),
      ratio = cropedWidth / cropedHeight,
      selectWidth = (cropedWidth) * (naturalWidth/scaledWidth),
      selectHeight = (cropedHeight) * (naturalHeight/scaledHeight);
      this.element.css({'height':scaledHeight,'width':scaledWidth});


      var img = $.Jcrop($picture, {
        trueSize: [naturalWidth, naturalHeight],
        setSelect: [0, 0, selectWidth, selectHeight],
        aspectRatio: ratio,
        keySupport: false,
        onSelect: function(c) {
          self.trigger("select", c);
        }
      });
      self.set("img", img);
    }
  });

  exports.run = function() {

    var $form = $("#templet-crop-form"),
      $picture = $("#templet-crop");

    var imageCrop = new ImageModalCrop({
      element: "#templet-crop",
      group: "default",
      cropedWidth: 360,
      cropedHeight: 360
    });

    imageCrop.on("afterCrop", function(response){
      var url = $("#upload-templet-btn").data("gotoUrl");

      $.post(url, { image: response }, function(data) {
        $(".product-img").attr('src', data.img);
        $("#img").val(data.file.uri);

        $('#modal').modal('hide');
      });
    });

    $("#upload-templet-btn").click(function(e){
      e.stopPropagation();

      imageCrop.crop({
              imgs: {
                large: [360, 360],
              }
      });

    })

    $('.go-back').click(function(){
      history.go(-1);
    });
  };

});
