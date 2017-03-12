import EsImageCrop from 'common/es-image-crop';
import notify from 'common/notify';

class CoverCrop {
  constructor(props) {
    this.element = props.element;
    this.avatarCrop = props.avatarCrop;
    this.saveBtn = props.saveBtn;
    this.init();
  }

  init() {
    let imageCrop = this.imageCrop();
    this.initEvent(imageCrop);
  }

  initEvent(imageCrop) {
    $(this.saveBtn).on('click', (event) => {
      event.stopPropagation();
      const $this = $(event.currentTarget);

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

  imageCrop() {
    let imageCrop = new EsImageCrop({
      element: this.avatarCrop,
      cropedWidth: 200,
      cropedHeight: 200
    });

    imageCrop.afterCrop = (response) => {
      let $saveBtn = $(this.saveBtn);
      
      let url = $saveBtn.data('url');
      
      $.post(url, { images: response }, (response) => {
        if (response.status === 'success') {
          $("#profile_avatar").val(response.avatar);
          $("#user-profile-form img").attr('src', response.avatar);
          $("#profile_avatar").blur();
          $("#modal").modal('hide');
          
          notify('success',Translator.trans('上传成功'));
        } else {
          notify('danger',Translator.trans('上传失败,请重试'));
          $saveBtn.button('reset');
        }
      });
    }
    return imageCrop
  }

}

new CoverCrop({
  element: '#avatar-crop-form',
  avatarCrop: '#avatar-crop',
  saveBtn: '#upload-avatar-btn',
})
