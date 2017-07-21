define(function(require, exports, module) {

  var imageCrop;
  var ImageCrop = require('edusoho.imagecrop');

  exports.run = function() {

    if(imageCrop)
    {
      imageCrop.destroy();
    }

    setTimeout(function(){
      imageCrop = new ImageCrop({
        element: "#templet-crop",
        group: 'system',
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
    }, 100);

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