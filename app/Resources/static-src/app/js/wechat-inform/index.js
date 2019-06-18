import { isMobileDevice } from 'common/utils';
import 'store';
const WECHAT_QRCODE_SHOW = 'WECHAT_QRCODE_SHOW_';

export default class wechatInform {
  constructor() {
    this.$section = $('.js-wechat-inform');
    this.$pendant = $('.js-wechat-pendant');
    this.$qrcode = $('.js-wechat-qrcode');
    this.$mask = $('.js-wechat-mask');
    this.todayDate = new Date().getDate();
    this.key = WECHAT_QRCODE_SHOW + this.$section.data('userId');
    this.init();
  }

  init() {
    if (!store.get(this.key) || store.get(this.key) != this.todayDate) {
      this.$section.removeClass('hidden');
    }
    this.bindEvent();
    this.initImg();
  }

  bindEvent() {
    this.$section.on('click', '.js-wechat-close-btn', (event) => this.closeWechatInform(event));
    this.$section.on('click', '.js-wechat-pendant', (event) => this.showQrcode(event));
  }
  
  closeWechatInform(event) {
    if (!store.get(this.key) || store.get(this.key) != this.todayDate) {
      store.set(this.key, this.todayDate);
    }
    const $target = $(event.currentTarget);
    event.stopPropagation();
    $target.parent().hide();
    let messageClass;
    if (isMobileDevice()) {
      this.$mask.hide();
      messageClass = 'cd-text-sm';
      $('body').removeClass('wechat-inform-body');
    } else {
      messageClass = 'cd-text-md';
    }
    cd.message({
      type: 'info',
      message: Translator.trans('wechat.notification.homepage.open_tip', {messageClass: messageClass}),
      delay: '3000',
      action: {
        title: Translator.trans('wechat.notification.homepage.open_tip_title', {messageClass: messageClass}),
        url: this.$section.data('url')
      },
    })
  }

  showQrcode(event) {
    const $target = $(event.currentTarget);
    if (isMobileDevice()) {
      $('body').addClass('wechat-inform-body');
      this.$mask.show();
    }
    $target.addClass('hidden');
    this.$qrcode.removeClass('hidden');
  }

  initImg() {
    var $target = $('.js-wechat-pendant');
    if (typeof($target.data('url')) != 'undefined') {
      $.get($target.data('url'), res => {
        $('.wechat-inform-qrcode__img').attr('src', res.img);
      });
    }
  }

}

new wechatInform();