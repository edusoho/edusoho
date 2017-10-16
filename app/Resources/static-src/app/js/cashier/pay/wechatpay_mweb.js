import BasePayment from './payment';

export default class WechatPayMweb extends BasePayment {

	afterTradeCreated(res) {
		document.querySelector('.order-pay-list').insertAdjacentHTML('afterend', '<div id="two">two</div>');
		setTimeout(function(){
			location.href = res.mwebUrl;
		},1500);
		this.startInterval(res.tradeSn);
	}

	startInterval(tradeSn) {
		window.intervalWechatId = setInterval(this.checkIsPaid.bind(this, tradeSn), 2000);
	}

	checkIsPaid(tradeSn) {
		document.querySelector('.order-pay-list').insertAdjacentHTML('afterend', '<div id="one">one</div>');
		BasePayment.getTrade(tradeSn).then(res => {
			if (res.isPaid) {
				location.href = res.paidSuccessUrl;
			}
		})
	}
}
