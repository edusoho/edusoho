import EsImageCrop from 'common/es-image-crop.js';

let imageCrop = new EsImageCrop({
  element: '#classroom-picture-crop',
  group: 'course',
  cropedWidth: 525,
  cropedHeight: 350
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
      large: [525, 350],
      middle: [345, 230],
      small: [213, 142],
    }
  });
});

$('.go-back').click(function(){
  history.go(-1);
});