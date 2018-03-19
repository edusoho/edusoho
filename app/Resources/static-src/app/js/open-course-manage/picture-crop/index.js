import EsImageCrop from 'common/es-image-crop.js';
import notify from 'common/notify';

class CoverCrop {
  constructor() {
    this.init();
  }

  init() {
    let imageCrop = new EsImageCrop({
      element: '#course-picture-crop',
      cropedWidth: 480,
      cropedHeight: 270
    });

    imageCrop.afterCrop = function(response) {
      let $saveBtn = $('.crop-picture-save-btn');
      let url = $saveBtn.data('url');

      $.post(url, { images: JSON.stringify(response) })
      .success((response) => {
        if (response.code) {
          $('#course-form').find('img').attr('src', response.cover);
          $('#modal').modal('hide');
        } else {
          notify('danger',Translator.trans('upload_fail_retry_hint'));
          $saveBtn.button('reset');
        }
      })
      .error((response) => {
        notify('danger',Translator.trans('upload_fail_retry_hint'));
        $saveBtn.button('reset');
      });
    };

    $(".crop-picture-save-btn").click(function(event) {
      $(event.currentTarget).button('loading');
      event.stopPropagation();
      imageCrop.crop({
        imgs: {
          large: [480, 270],
          middle: [304, 171],
          small: [96, 54],
        }
      });
    })

  }
}

new CoverCrop();
