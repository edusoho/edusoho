import EsImageCrop from 'common/es-image-crop.js';

let $form = $('#avatar-crop-form'),
  $picture = $('#avatar-crop');

let imageCrop = new EsImageCrop({
  element: '#logo-crop',
  group: 'group',
  cropedWidth: 200,
  cropedHeight: 200
});

imageCrop.afterCrop = function (response) {
  let url = $('#upload-picture-btn').data('url');
  $.post(url, { images: response }, function () {
    document.location.href = $('#upload-picture-btn').data('reloadUrl');
  });
};

$('#upload-picture-btn').click(function (e) {
  e.stopPropagation();
  imageCrop.crop({
    imgs: {
      logo: [200, 200]
    }
  });
});
