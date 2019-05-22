import { isMobileDevice } from 'common/utils';

export default class wechatInform {
	constructor() {
		this.$section = $('.js-wechat-inform');
		this.$pendant = $('.js-wechat-pendant');
		this.$qrcode = $('.js-wechat-qrcode');
		this.$entrance = $('.js-inform-wechat-entrance');
		this.$mask = $('.js-wechat-mask');
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
		const self = this;
		const $target = $(event.currentTarget);
		event.stopPropagation();
		$target.parent().hide();

		if (isMobileDevice()) {
			this.$mask.hide();
			$('body').removeClass('wechat-inform-body');
		} else {
			$('html, body').animate({scrollTop: '0px'}, 800, () => {
				self.$entrance.fadeIn('normal', () => {
					setTimeout(() => {
						self.$entrance.fadeOut('normal');
					}, 3000);
				});
			});
		}
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
}

new wechatInform();