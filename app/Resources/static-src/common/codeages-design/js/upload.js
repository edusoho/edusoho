class Upload {
  constructor(props) {
    Object.assign(this, {
      parent: document,
      type: 'normal',
      fileTypes: ['image/bmp', 'image/jpeg', 'image/png'],
      fileSize: 2 * 1024 * 1024,
    }, props);

    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    $(this.parent).on('change.cd.local-upload', this.el, event => this.uploadHandle(event));
  }

  uploadHandle(event) {
    let target = event.currentTarget;
    let self = this;

    let fr = new FileReader();

    if (!self.catch(event)) {
      return;
    };

    fr.onload = function(e) {
      let src = e.target.result;

      try {
        self[self.type](event, src);
      } catch(e) {
        throw new Error(`${self.type} type does not exist`);
      }
    }

    fr.readAsDataURL($(target)[0].files[0]);
  }

  catch(event) {
    // 文件大小限制
    const FILE_SIZE_LIMIT = 'FILE_SIZE_LIMIT';
    // 文件类型限制
    const FLIE_TYPE_LIMIT = 'FLIE_TYPE_LIMIT';

    let target = event.currentTarget;
    let file = $(target)[0].files[0];

    if (file.size > this.fileSize) {
      this.error(FILE_SIZE_LIMIT);
      return false;
    }
    
    if (!this.fileTypes.includes(file.type)) {
      this.error(FLIE_TYPE_LIMIT);
      return false;
    }

    return true;
  }

  normal(event, src) {
    let $this = $(event.currentTarget);
    let $target = $($this.data('target'));

    if ($target) {
      $target.css('background-image', `url(${src})`);
      this.success(event, $target);
    } else {
      this.success(event, src);
    }
  }

  crop(event, src) {
    let image = new Image();
    let $this = $(event.currentTarget);
    let self = this;

    image.onload = function() {
      let width = image.width;
      let height = image.height;
      let cropWidth = $this.data('crop-width');
      let cropHeight = $this.data('crop-height');

      let scale = self.imageScale({
        naturalWidth: width,
        naturalHeight: height,
        cropWidth,
        cropHeight
      });

      let $image = $(image);
      
      $image.attr({
        'class': 'hidden',
        'data-natural-width': width,
        'data-natural-height': height,
        'width': scale.width,
        'height': scale.height,
      });

      self.success(event, $image);
    };

    image.src = src;
  }

  imageScale({ naturalWidth, naturalHeight, cropWidth, cropHeight }) {
    let width = cropWidth;
    let height = cropHeight;
  
    let naturalScale = naturalWidth / naturalHeight;
    let cropScale = cropWidth / cropHeight;
  
    if (naturalScale > cropScale) {
      width = naturalScale * cropWidth;
    } else {
      height =  cropHeight / naturalScale;
    }
  
    return {
      width,
      height
    }
  }

  success() {
    console.log('upload.success');
  }

  error(code) {
    console.log('upload.error', code);
  }
}

function upload(props) {
  return new Upload(props);
}

// HOW TO USE
// cd.upload({
//   el: '',
//   type: 'normal',
//   success() {

//   },
//   error(code) {

//   }
// })

export default upload;