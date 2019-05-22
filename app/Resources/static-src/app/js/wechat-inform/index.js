export default class wechatInform {
	constructor() {
		this.$section = $('.js-wechat-inform');
		this.$pendant = $('.js-wechat-pendant');
		this.$qrcode = $('.js-wechat-qrcode');
		this.$entrance = $('.js-inform-wechat-entrance');
		this.init();
	}
	
	init() {
		this.bindEvent();
	}

	bindEvent() {
		this.$section.on('click', '.js-wechat-close-btn', (event) => this.closeWechatInform(event));
    this.$section.on('click', '.js-wechat-pendant', (event) => this.showQrcode(event));
	}
	closeWechatInform(event) {
		const $target = $(event.currentTarget);
		event.stopPropagation();
		$target.parent().hide();
		this.$entrance.fadeIn('normal', () => {
			setTimeout(() => {
				this.$entrance.fadeOut('normal');
			}, 3000);
		});
	}
	showQrcode(event) {
		const $target = $(event.currentTarget);
		$target.addClass('hidden');
		this.$qrcode.removeClass('hidden');
	}
}

new wechatInform();