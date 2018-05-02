import Api from 'common/api';

export default class Drag {
  constructor(bar, target) {
    this.$element = bar;
    this.$target = target;
    this.params = {
      top: 0,
      left: 0,
      currentX: 0,
      currentY: 0,
      flag: false,
      currentLeft: 0,
      currentTop: 0
    };
    this.dragCaptchaToken = null;
    this.init();
  }

  init() {
    this.initDragCaptcha();
    this.getLocation(this.$element[0]);
    this.initEvent();
  }

  initDragCaptcha() {
    Api.dragCaptcha.get({
      before() {
        $('.js-drag-img-mask').removeClass('hidden');
        const $jigsawBg = $('.js-jigsaw-bg');
        if ($jigsawBg.length) {
          $jigsawBg.remove();
          $('.js-jigsaw-placeholder').toggleClass('hidden');
          $('.js-jigsaw').attr('src', '');
        }
      }
    }).then((res) => {
      this.loadingImg(res.url, res.jigsaw);
      this.dragCaptchaToken = res.token;
    });
  }

  loadingImg(url, src) {
    const img = new Image();
    img.onload = () => {
      $(img).prependTo('.js-drag-img');
      $('.js-drag-img-mask').addClass('hidden');
      $('.js-jigsaw-placeholder').toggleClass('hidden');
      $('.js-jigsaw').attr('src', src);
    };
    img.className = 'js-jigsaw-bg drag-img__bg';
    img.src = url;
  }

  initEvent() {
    const $element = this.$element;
    $element.on('mousedown touchstart', (event) => {
      this.startDrag(event);
    });

    $(document).on('mouseup touchend', (event) => {
      this.stopDrag(event);
    });
    $(document).on('mousemove touchmove', (event) => {
      this.dragMove(event);
    });
  }

  startDrag(e) {
    const params = this.params;
    params.flag = true;
    const currentX = e.clientX ? e.clientX.toFixed(2) : e.originalEvent.targetTouches[0].pageX.toFixed(2);
    params.currentX = currentX;
    const startTimeStamp = Date.parse(new Date());
    console.log(startTimeStamp);
  }

  stopDrag(e) {
    const $element = this.$element;
    const $target = this.$target;
    const params = this.params;
    if (!params.flag) {
      return;
    }
    const endTimeStamp = Date.parse(new Date());
    console.log(endTimeStamp);
    this.setCss($element[0], 'cursor', 'pointer');
    params.flag = false;
    this.getLocation($element[0]);

    if (params.currentLeft) {
      const $jigsaw = $('.js-jigsaw');
      const positionX = this.calPositionX($jigsaw);
      const data = { token: this.dragCaptchaToken, jigsaw: positionX };
      console.log(positionX);
      Api.dragCaptcha.validate({ params: data }).then((res) => {
        if (res.status === 'invalid') {
          this.resetLocation($element[0], $target[0]);
          cd.message({
            type: 'danger',
            message: Translator.trans('validate.fail')
          });
        } else if (res.status === 'expired') {
          this.resetLocation($element[0], $target[0]);
          this.initDragCaptcha();
        } else {
          this.validateSuccess($element[0], positionX);
        }
      });
    }
  }

  dragMove(e) {
    const $element = this.$element;
    const $target = this.$target;
    const params = this.params;
    if (!params.flag) return;
    const currentX = e.clientX ? e.clientX.toFixed(2) : e.originalEvent.targetTouches[0].pageX.toFixed(2);
    const disX = currentX - params.currentX;
    const width = $element.parent().width() - $element.width();
    let leftNum = parseInt(params.left) + disX;

    if (leftNum <= 0) {
      leftNum = 0;
    }
    if (leftNum >= width) {
      leftNum = width;
    }
    params.currentLeft = leftNum;
    const left = leftNum + 'px';
    this.setCss($element[0], 'left', left);
    this.setCss($target[0], 'left', left);
    this.setCss($element[0], 'cursor', 'move');
    $('.js-drag-bar-tip').addClass('hidden');
    const movingLeft = leftNum + 20 + 'px';
    $('.js-drag-bar-mask').css('width', movingLeft);
  }

  calPositionX($target) {
    const rate = $target[0].naturalWidth / $target.width();
    const positionX = (this.params.currentLeft * rate).toFixed(2);
    return positionX;
  }

  validateSuccess(target, positionX) {
    cd.message({
      type: 'success',
      message: Translator.trans('validate.success')
    });
    const $tokenDom = $('[name="drag_captcha_token"]');
    $tokenDom.val(this.dragCaptchaToken);
    const $dargForm = $tokenDom.closest('.form-group');
    $dargForm.removeClass('has-error');
    $dargForm.find('.jq-validate-error').remove();
    $('[name="jigsaw"]').val(positionX);
    $(document).unbind('mousemove touchmove');
    $(document).unbind('mouseup touchend');
    this.setCss(target, 'cursor', 'not-allowed');
  }

  getLocation(target) {
    if (this.getCss(target, 'left') !== 'auto') {
      this.params.left = this.getCss(target, 'left');
    }
  }

  resetLocation(element, target) {
    this.setCss(element, 'left', '0px');
    this.setCss(target, 'left', '0px');
    this.getLocation(element);
    $('.js-drag-bar-mask').css('width', '0px');
    $('.js-drag-bar-tip').toggleClass('hidden');
  }

  getCss(o, key) {
    return o.currentStyle ? o.currentStyle[key] : document.defaultView.getComputedStyle(o, false)[key];
  }

  setCss(o, key, value)  {
    o.style[key] = value;
  }

}

