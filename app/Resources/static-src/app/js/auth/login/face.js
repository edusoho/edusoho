export default class Face {
  constructor(options) {
    this.$loginDom = $('.js-sts-login');
    this.$wrap = options.wrap;
    this.element = options.element;
    this.$qrcode = this.$loginDom.find('.js-sts-login-qrcode img');
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
    $.ajax({
      type: 'post',
      url: this.$loginDom.data('url'),
      dataType: 'json',
      success: (data) => {
        console.log(data);
        this.$qrcode.attr('src', data.qrcode);
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
