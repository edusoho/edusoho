export default class wechatInform {
  constructor() {
    this.$qrcode = $('.js-qrcode');
    this.init();
  }

  init() {
    this.initImg();
  }

  initImg() {
    let $target = this.$qrcode;
    if (typeof($target.data('url')) != 'undefined') {
      $.get($target.data('url'), res => {
        $target.attr('src', res.img);
      });
    }
  }

}

new wechatInform();