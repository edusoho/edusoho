import Api from 'common/api';
import { strToBase64 } from 'common/utils';
import Emitter from 'component-emitter';

export default class Drag extends Emitter{
  constructor(bar, target, data) {
    super();
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
    this.data = Object.assign({times: 2}, data);
    this.dragCaptchaToken = null;
    this.init();
  }

  init() {
    this.initDragCaptcha();
    this.getLocation(this.$element[0]);
  }

  initDragCaptcha() {
    let self = this;
    Api.dragCaptcha.get({
      before() {
        $('.js-drag-img').css('minHeight', $('.js-jigsaw').height());
        $('.js-drag-img-mask').removeClass('hidden');
        $('.js-jigsaw-bg').remove();
        $('.js-jigsaw').attr('src', '');
        self.setCss(self.$element[0], 'cursor', 'pointer');
        self.resetLocation(self.$element[0], self.$target[0]);
        $('[name="dragCaptchaToken"]').val('');
        self.initEvent();
      },
      data: this.data
    }).then((res) => {
      this.loadingImg(res.url, res.jigsaw);
      this.dragCaptchaToken = res.token;
    });
  }

  loadingImg(url, src) {
    const img = new Image();
    img.src = url;
    img.className = 'js-jigsaw-bg drag-img__bg';
    img.onload = () => {
      if ($('.js-jigsaw-bg').length > 0) {
        return;
      }
      
      $(img).prependTo('.js-drag-img');
      $('.js-drag-img-mask').addClass('hidden');
      $('.js-jigsaw').attr('src', src);
    };
  }

  initEvent() {
    this.unbindEvent();
    const $document = $(document);
    this.$element.on('mousedown.drag.captcha touchstart.drag.captcha', (event) => {
      this.startDrag(event);
    });

    $document.on('mouseup.drag.captcha touchend.drag.captcha', (event) => {
      this.stopDrag(event);
    });

    $document.on('mousemove.drag.captcha touchmove.drag.captcha', (event) => {
      this.dragMove(event);
    });
  }

  unbindEvent() {
    this.$element.unbind('mousedown.drag.captcha touchstart.drag.captcha');
    $(document).unbind('mousemove.drag.captcha touchmove.drag.captcha mouseup.drag.captcha touchend.drag.captcha');
  }

  startDrag(e) {
    e.preventDefault();
    const params = this.params;
    params.flag = true;
    const currentX = e.clientX ? e.clientX.toFixed(2) : e.originalEvent.targetTouches[0].pageX.toFixed(2);
    params.currentX = currentX;
  }

  stopDrag(e) {
    let self = this;
    const $element = this.$element;
    const $target = this.$target;
    const params = this.params;
    if (!params.flag) {
      return;
    }

    params.flag = false;
    this.getLocation($element[0]);

    if (params.currentLeft) {
      const $jigsawBg = $('.js-jigsaw-bg');
      let positionX = this.calPositionX($jigsawBg);
      let token = this._getToken(this.dragCaptchaToken, positionX);
      let data = {token: token};

      Api.dragCaptcha.validate({ params: data }).then((res) => {
        self.validateSuccess($element[0], token);
      }).catch(function() {
        self.initDragCaptcha();
        self.emit('error');
      });
    }
  }

  dragMove(e) {
    const $element = this.$element;
    const $target = this.$target;
    const params = this.params;
    if (!params.flag) return;
    e.preventDefault();
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
    const rate = ($target[0].naturalWidth / $target.width()).toFixed(2);
    const paramsLeft = this.params.currentLeft.toFixed(2);
    const positionX = (paramsLeft * rate).toFixed(2);
    return positionX;
  }

  validateSuccess(target, token) {
    cd.message({
      type: 'success',
      message: Translator.trans('validate.success')
    });
    const $tokenDom = $('[name="dragCaptchaToken"]');
    $tokenDom.val(token);
    const $dargForm = $tokenDom.closest('.form-group');
    $dargForm.removeClass('has-error');
    $dargForm.find('.jq-validate-error').remove();
    this.unbindEvent();
    this.setCss(target, 'cursor', 'not-allowed');
    this.emit('success',{ token: token });
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
    $('.js-drag-bar-tip').removeClass('hidden');
  }

  getCss(o, key) {
    return o.currentStyle ? o.currentStyle[key] : document.defaultView.getComputedStyle(o, false)[key];
  }

  setCss(o, key, value)  {
    o.style[key] = value;
  }

  _getToken(token, position) {
    let dragToken = {token: token, captcha: position};
    token = JSON.stringify(dragToken);

    return [...strToBase64(token)].reverse().join('');
  }
}

