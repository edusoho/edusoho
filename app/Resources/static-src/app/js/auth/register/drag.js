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
    const self = this;
    Api.dragCaptcha.get({
      before() {
        $('.js-drag-img-mask').toggleClass('hidden');
      }
    }).then((res) => {
      $('.js-drag-img-mask').toggleClass('hidden');
      console.log($('.js-jigsaw-bg').length);
      if (!$('.js-jigsaw-bg').length) {
        self.loadingImg(res.url, res.jigsaw);
      }
      this.dragCaptchaToken = res.token;
    });
  }

  loadingImg(url, src) {
    const img = new Image();
    img.onload = () => {
      $(img).prependTo('.js-drag-img');
      $('.js-jigsaw-placeholder').toggleClass('hidden');
      $('.js-jigsaw').attr('src', src);
    };
    img.className = 'js-jigsaw-bg drag-img__bg';
    img.src = url;
  }

  initEvent() {
    const $element = this.$element;
    $element.mousedown((event) => {
      this.startDrag(event);
    });
    $(document).mouseup((event) => {
      this.stopDrag(event);
    });
    $(document).mousemove((event) => {
      this.dragMove(event);
    });
  }

  startDrag(event) {
    const params = this.params;
    params.flag = true;
    const e = event;
    params.currentX = e.clientX;
    params.currentY = e.clientY;
  }

  stopDrag(event) {
    const $element = this.$element;
    const $target = this.$target;
    const params = this.params;
    if (!params.flag) {
      return;
    }
    this.setCss($element[0], 'cursor', 'pointer');
    params.flag = false;
    this.getLocation($element[0]);

    if (params.currentLeft) {
      const rate = 40 / $('.js-jigsaw').width();
      const positionX = (params.currentLeft * rate).toFixed(2);
      console.log(positionX);
      const data = { token: this.dragCaptchaToken, jigsaw: positionX };
      Api.dragCaptcha.validate({ params: data }).then((res) => {
        if (res.status === 'invalid') {
          this.resetLocation($element[0], $target[0]);
          cd.message({
            type: 'danger',
            message: Translator.trans('validate.fail')
          });
        } else if (res.status === 'expired') {
          this.resetLocation($element[0], $target[0]);
          $('.js-jigsaw-bg').remove();
          $('.js-jigsaw-placeholder').toggleClass('hidden');
          $('.js-jigsaw').attr('src', '');
          this.initDragCaptcha();
        } else {
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
          $(document).unbind('mousemove');
          $(document).unbind('mouseup');
          this.setCss($element[0], 'cursor', 'not-allowed');
        }
      });
    }
  }

  dragMove(event) {
    const $element = this.$element;
    const $target = this.$target;
    const params = this.params;
    if (!params.flag) return;
    const e = event;
    e.preventDefault();

    const nowX = e.clientX;
    const nowY = e.clientY;
    const disX = nowX - params.currentX;
    const disY = nowY - params.currentY;
    const width = $element.parent().width() - $element.width();
    let leftNum = parseInt(params.left) + disX;

    if (leftNum <= 0) {
      leftNum = 0;
    }
    if (leftNum >= width) {
      leftNum = width;
    }

    const left = leftNum + 'px';
    const movingLeft = leftNum + 20 + 'px';
    this.setCss($element[0], 'left', left);
    this.setCss($target[0], 'left', left);
    this.setCss($element[0], 'cursor', 'move');
    $('.js-drag-bar-tip').addClass('hidden');
    $('.js-drag-bar-mask').css('width', movingLeft);
    params.currentLeft = leftNum;
  }

  getLocation(target) {
    if (this.getCss(target, 'left') !== 'auto') {
      this.params.left = this.getCss(target, 'left');
    }
  }

  resetLocation(element, target) {
    this.setCss(element, 'left', '0px');
    this.setCss(target, 'left', '0px');
    $('.js-drag-bar-mask').css('width', '0px');
    $('.js-drag-bar-tip').toggleClass('hidden');
    this.getLocation(element);
  }

  getCss(o, key) {
    return o.currentStyle ? o.currentStyle[key] : document.defaultView.getComputedStyle(o, false)[key];
  }

  setCss(o, key, value)  {
    o.style[key] = value;
  }

}

