import EsImageCrop from 'common/es-image-crop';

class LocalImageCrop {
  constructor(props) {
    this.cropImg = props.cropImg;
    this.saveBtn = props.saveBtn;
    this.selectBtn = props.selectBtn;
    this.imgs = props.imgs;
    this.group = props.group;
    this.lastFile = {};
    this.uploadInput = props.uploadInput || '.js-upload-input';
    this.modal = props.modal || '#modal';

    this.init();
  }

  init() {
    this.initImage();

    let imageCrop = this.imageCrop();
    this.initEvent(imageCrop);
  }

  initImage($sourceImg) {
    let imageAttrJson = localStorage.getItem('crop_image_attr');
    if (imageAttrJson === 'get_from_dom') {
      imageAttrJson = $('[name=crop_image_attr]').val();
    }
    let imageAttr = JSON.parse(imageAttrJson);

    $(this.cropImg).attr({
      'src': imageAttr.src,
      'width': imageAttr.width,
      'height': imageAttr.height,
      'data-natural-width': imageAttr['natural-width'],
      'data-natural-height': imageAttr['natural-height'],
    });

    localStorage.removeItem('crop_image_attr');
  }

  initEvent(imageCrop) {
    $(this.saveBtn).on('click', event => this.saveEvent(event, imageCrop));
    $(this.selectBtn).on('click', event => this.selectEvent(event));
  }

  saveEvent(event, imageCrop) {
    event.stopPropagation();
    const $this = $(event.currentTarget);
    console.log('start crop');
    imageCrop.crop({
      imgs: this.imgs,
      post: false
    });
    $this.button('loading');
  }

  selectEvent(event) {
    $(this.uploadInput).click();
  }

  imageCrop() {
    let imageCrop = new EsImageCrop({
      element: this.cropImg,
      cropedWidth: this.imgs.large[0],
      cropedHeight: this.imgs.large[1],
      group: this.group
    });
    this.lastFile = $(this.uploadInput)[0].files[0];
    imageCrop.afterCrop = (res) => {
      this.afterCrop(res);
    };

    return imageCrop;
  }

  afterCrop(cropOptions) {
    let fromData = new FormData();
    let $modal =  $(this.modal);
    let $input = $(this.uploadInput);

    fromData.append('token', $input.data('token'));
    const file = $input[0].files[0] ? $input[0].files[0]: this.lastFile;
    fromData.append('file', file);

    let uploadImage = function() {
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: $input.data('fileUpload'),
          type: 'POST',
          cache: false,
          data: fromData,
          processData: false,
          contentType: false,
        }).done(function(data) {
          resolve(data);
        });
      });
    };

    let cropImage = function(res) {
      return new Promise(function(resolve, reject) {
        $.post($input.data('crop'), cropOptions, function(data) {
          if(data) {
            console.log("进来了");
            const name = $input.attr('id');
            $($input.data('targetImg')).attr('src', data[0].url)
            addInputVal(JSON.stringify(data),'cropImageAttr')
            $(`input[name=${name}]`).val(JSON.stringify(data))
          }
          resolve(data);
        }).always(function() {
          $input.val('');
          $modal.modal('hide');
        });;
      });
    };

    let addInputVal = (res, name='') => {
      $(document).ready(function() {
        let newInput = $('<input>');

        newInput.attr({
            'type': 'hidden',
            'name': name,
            'value': res
        });

        $('body').append(newInput);
      });
    }

    let saveImage = function(res) {
      return new Promise(function(resolve, reject) {
        $.post($input.data('saveUrl'), { images: res }, function(data) {
          if (data.image) {
            $($input.data('targetImg')).attr('src', data.image)

            if($('input[name="crop_image_attr"]')) {
              $('input[name="crop_image_attr"]').val(data.image);
            }
            
            addInputVal(data.image,'cropImageAttr')
            cd.message({ type: 'success', message: Translator.trans('site.upload_success_hint') });
          }
        }).error(function() {
          cd.message({ type: 'danger', message: Translator.trans('site.upload_fail_retry_hint') });
        }).always(function() {
          $input.val('');
          $modal.modal('hide');
        });
      });
    };

    uploadImage().then(function(res) {
      return cropImage(res);
    }).then(function(res) {
      // return saveImage(res);
    }).catch(function(res) {
      console.log(res);
    });
   
  }
}

export default LocalImageCrop;