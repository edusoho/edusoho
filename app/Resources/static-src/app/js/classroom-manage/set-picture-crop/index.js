import EsImageCrop from 'common/es-image-crop.js';

let imageCrop = new EsImageCrop({
  element: '#classroom-picture-crop',
  group: 'course',
  cropedWidth: 540,
  cropedHeight: 304
});

imageCrop.afterCrop = function(response) {
  let url = $('#upload-picture-btn').data('url');
  $.post(url, {images: response}, function(){
    document.location.href=$('#upload-picture-btn').data('gotoUrl');
  });
};

$('#upload-picture-btn').click(function(e) {
  e.stopPropagation();
  $('#upload-picture-btn').button('loading');
  imageCrop.crop({
    imgs: {
      large: [540, 304],
      middle: [354, 200],
      small: [219, 124],
    }
  });
});

$('.go-back').click(function(){
  history.go(-1);
});