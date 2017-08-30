import EsImageCrop from 'common/es-image-crop';

class CoverCrop {
  constructor(props) {
    this.avatarCrop = props.avatarCrop;
    this.saveBtn = props.saveBtn;
    this.init();
  }

  init() {
    this.imageInit();
    let imageCrop = this.imageCrop();
    this.initEvent(imageCrop);
  }

  initEvent(imageCrop) {
    $(this.saveBtn).on('click', (event) => {
      event.stopPropagation();
      const $this = $(event.currentTarget);
      console.log('start crop')
      imageCrop.crop({
        imgs: {
          large: [200, 200],
          medium: [120, 120],
          small: [48, 48]
        }
      });

      $this.button('loading');
    });
  }

  imageInit() {
    let sourceImg = $('#upload-source-img');
    $(this.avatarCrop).attr({
      'src': sourceImg.attr('src'),
      'width': sourceImg.attr('width'),
      'height': sourceImg.attr('height'),
      'data-natural-width': sourceImg.data('natural-width'),
      'data-natural-height': sourceImg.data('natural-height')
    });

    sourceImg.remove();
  }

  imageCrop() {
    console.log('init')
    let imageCrop = new EsImageCrop({
      element: this.avatarCrop,
      cropedWidth: 200,
      cropedHeight: 200
    });

    imageCrop.afterCrop = (res) => {
      let $saveBtn = $(this.saveBtn);
      
      let url = $saveBtn.data('url');

      console.log('res', res);

      $.post(url, { images: res }, (res) => {
        if (res.status === 'success') {
          $('#profile-avatar').attr('src', res.avatar);

          $('#modal').modal('hide');
          
          notify('success', Translator.trans('site.upload_success_hint'));
        } else {
          notify('danger', Translator.trans('upload_fail_retry_hint'));
          $saveBtn.button('reset');
        }
      });
    }

    return imageCrop;
  }
}

new CoverCrop({
  avatarCrop: '#avatar-crop',
  saveBtn: '#save-btn'
})

