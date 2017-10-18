import BasePayment from './payment';
import 'store';

export default class WechatPayMweb extends BasePayment {

	startInterval(){
		return true;
	}
	afterTradeCreated(res) {
		store.set('trade_'+this.getURLParameter('sn'), res.tradeSn);
		this.checkOrderStatus();
		location.href = res.mwebUrl;
	}
}
