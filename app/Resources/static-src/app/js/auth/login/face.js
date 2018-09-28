export default class Face {
  constructor(options) {
    this.$loginDom = $('.js-sts-login');
    this.$wrap = options.wrap;
    this.element = options.element;
    this.init();
  }
  init() {
    this.bindEvent();
  }

  bindEvent() {
    this.$loginDom.on('click', '.js-login-back', () => this.back());
    this.$wrap.on('click', '.js-sts-login-link', () => this.showQrcode());
  }

  back() {
    this.toggleShow();
  }

  showQrcode(element) {
    const $qrcodeWrap = $('.js-sts-login');
    $.ajax({
      type: 'post',
      url: $qrcodeWrap.data('url'),
      dataType: 'json',
      success: (data) => {
        console.log(data);
        $qrcodeWrap.find('.js-sts-login-qrcode img').attr('src', data.qrcode);
        this.toggleShow();
      }
    });
  }

  toggleShow() {
    $(this.element).toggleClass('hidden');
    if (this.$wrap.hasClass('login-modal')) {
      this.$wrap.find('.modal-footer').toggleClass('hidden');
    }
  }
}
