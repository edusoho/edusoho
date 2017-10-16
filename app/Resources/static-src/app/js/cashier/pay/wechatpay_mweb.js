import BasePayment from './payment';

export default class WechatPayMweb extends BasePayment {

	afterTradeCreated(res) {
		location.href = res.mwebUrl;
		this.startInterval(res.tradeSn);
	}

	startInterval(tradeSn) {
		window.intervalWechatId = setInterval(this.checkIsPaid.bind(this, tradeSn), 2000);
	}

	checkIsPaid(tradeSn) {
		BasePayment.getTrade(tradeSn).then(res => {
			if (res.isPaid) {
				location.href = res.paidSuccessUrl;
			}
		});
	}
}
