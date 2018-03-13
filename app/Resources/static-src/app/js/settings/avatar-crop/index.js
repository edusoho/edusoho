import EsImageCrop from 'common/es-image-crop';

class CoverCrop {
  constructor(props) {
    this.element = props.element;
    this.avatarCrop = props.avatarCrop;
    this.saveBtn = props.saveBtn;
    this.goBack = props.goBack;
    this.init();
  }

  init() {
    let imageCrop = this.imageCrop();
    this.initEvent(imageCrop);
  }

  initEvent(imageCrop) {
    const $node = $(this.element);
    $node.on('click', this.goBack, (event) => this.goBackEvent(event));

    $node.on('click', this.saveBtn, (event) => {
      event.stopPropagation();
      imageCrop.crop({
        imgs: {
          large: [200, 200],
          medium: [120, 120],
          small: [48, 48]
        }
      });
    });
  }

  goBackEvent(event) {
    let $element = $(event.currentTarget);
    document.location.href = $element.data('gotoUrl');
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
      
      $.post(url, { images: response }, () => {
        document.location.href = $saveBtn.data('gotoUrl');
      });
    };
    return imageCrop;
  }

}

new CoverCrop({
  element: '#avatar-crop-form',
  avatarCrop: '#avatar-crop',
  saveBtn: '#upload-avatar-btn',
  goBack: '.js-go-back'
});