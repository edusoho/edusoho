import EsImageCrop from 'common/es-image-crop.js';
import notify from 'common/notify';

class CoverCrop {
  constructor(props) {
    this.element = props.element;
    this.avatarCrop = props.avatarCrop;
    this.saveBtn = props.saveBtn;
    this.init();
  }

  init() {
    let imageCrop = new EsImageCrop({
      element: this.avatarCrop,
      cropedWidth: 480,
      cropedHeight: 270
    });
    imageCrop.afterCrop = function(response) {
      let $saveBtn = $('.crop-picture-save-btn');
      let url = $saveBtn.data('url');

      $.post(url, { images: JSON.stringify(response) })
      .success((response) => {
        if (response.code) {
          $('#courseset-form').find('img').attr('src', response.cover);
          $('#cover').blur();
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

    $(this.saveBtn).click(function(event) {
      event.stopPropagation();
      $(event.currentTarget).button('loading');
      imageCrop.crop({
        imgs: {
          large: [480, 270],
          middle: [304, 171],
          small: [96, 54],
        }
      });

    })

    $('.go-back').click(function() {
      history.go(-1);
    });
  }
}


new CoverCrop({
  element: '#courseset-picture-crop-form',
  avatarCrop: '#courseset-picture-crop',
  saveBtn: '.crop-picture-save-btn',
});
