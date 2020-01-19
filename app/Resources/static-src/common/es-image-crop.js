import 'es-jcrop/js/Jcrop.js';

class EsImageCrop {
  constructor(config) {
    let self = this;
    this.config = $.extend({
      element: null,
      group: 'default'
    }, config);

    this.element = $(this.config.element);
    let $picture = this.element;
    let scaledWidth = $picture.attr('width'),
      scaledHeight = $picture.attr('height'),
      naturalWidth = $picture.data('naturalWidth'),
      naturalHeight = $picture.data('naturalHeight'),
      cropedWidth = this.config.cropedWidth,
      cropedHeight = this.config.cropedHeight,
      ratio = cropedWidth / cropedHeight,
      selectWidth = (cropedWidth) * (naturalWidth / scaledWidth),
      selectHeight = (cropedHeight) * (naturalHeight / scaledHeight);

    $picture.Jcrop({
      trueSize: [naturalWidth, naturalHeight],
      setSelect: [0, 0, selectWidth, selectHeight],
      aspectRatio: ratio,
      keySupport: false,
      allowSelect: false,
      onSelect(c) {
        self.onSelect(c);
      }
    });

    // $picture.css('height', scaledHeight);
  }

  crop(postData = {}) {
    console.log('crop');
    let self = this;
    let cropImgUrl = app.imgCropUrl;
    console.log(cropImgUrl);
    let newPostData = $.extend(self.element.data('Jcrop').ui.selection.last, postData, {
      width: this.element.width(),
      height: this.element.height(),
      group: self.config.group
    });

    //由于小数精度问题，jcrop计算出的x、y初始坐标可能小于0，比如-2.842170943040401e-14, 应当修正此类非法数据
    newPostData.x = newPostData.x > 0 ? newPostData.x : 0;
    newPostData.y = newPostData.y > 0 ? newPostData.y : 0;
    if (postData.post === false) {
      self.afterCrop(newPostData);
    } else {
      $.post(cropImgUrl, newPostData, function(response) {
        self.afterCrop(response);
      });
    }

  }

  onSelect() {
    //override it
  }

  afterCrop() {
    //override it
  }
}

export default EsImageCrop;
