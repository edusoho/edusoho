import { isMobileDevice } from 'common/utils';

export default class wechatInform {
	constructor() {
		this.$section = $('.js-wechat-inform');
		this.$pendant = $('.js-wechat-pendant');
		this.$qrcode = $('.js-wechat-qrcode');
		this.$mask = $('.js-wechat-mask');
		this.init();
	}

	init() {
		this.bindEvent();
    this.initImg();
	}

  bindEvent() {
    this.$section.on('click', '.js-wechat-close-btn', (event) => this.closeWechatInform(event));
    this.$section.on('click', '.js-wechat-pendant', (event) => this.showQrcode(event));
	}
  
	closeWechatInform(event) {
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
      message: `<span class="${messageClass}">“微信通知”可在“个人设置-第三方登录”开启。</span>`,
      delay: '100000',
      action: {
        title: `<span class="${messageClass}">前往开启</span>`,
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