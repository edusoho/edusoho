import BasePayment from './payment';

export default class WechatPayMweb extends BasePayment {

	afterTradeCreated(res) {
		location.href = res.mweb_url;
	}
}
