import BasePayment from './payment';

export default class WechatPayMweb extends BasePayment {

	afterTradeCreated(res) {
		document.querySelector('.order-pay-list').insertAdjacentHTML('afterend', '<div id="two">two</div>');
		setTimeout(function(){
			location.href = res.mwebUrl;
		},1500);
	}
}
