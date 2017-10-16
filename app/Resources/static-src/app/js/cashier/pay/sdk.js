import WechatPayNative from './wechatpay_native';
import AlipayLegacyExpress from './alipay_legacy_express';
import AlipayLegacyWap from './alipay_legacy_wap';
import LianlianpayWap from './lianlianpay_wap';
import LianlianpayWeb from './lianlianpay_web';
import WechatPayJs from './wechatpay_js';
import WechatPayMweb from './wechatpay_mweb';

export default class PaySDK {

  constructor(){
	  alert(new Date().toTimeString());
  }
  pay(params, options = {}) {
    let gateway = this.getGateway(params['payment'], params['isMobile'], params['openid']);
	  params.gateway = gateway;
    let paySdk = null;
    switch (gateway) {
      case 'WechatPay_Native':
        paySdk = this.wpn ? this.wpn : this.wpn = new WechatPayNative();
        break;
	    case 'WechatPay_MWeb':
		    paySdk = this.wpm ? this.wpm : this.wpm = new WechatPayMweb();
		    break;
      case 'WechatPay_Js':
        paySdk = this.wpj ? this.wpj : this.wpj = new WechatPayJs();
        break;
      case 'Alipay_LegacyExpress':
        paySdk = this.ale ? this.ale : this.ale = new AlipayLegacyExpress();
        break;
      case 'Alipay_LegacyWap':
        paySdk = this.alw ? this.alw : this.alw = new AlipayLegacyWap();
        break;
      case 'Lianlian_Wap':
        paySdk = this.llwp ? this.llwp : this.llwp = new LianlianpayWap();
        break;
      case 'Lianlian_Web':
        paySdk = this.llwb ? this.llwb : this.llwb = new LianlianpayWeb();
        break;
    }

    paySdk.options = Object.assign({
      'showConfirmModal': 1
    }, options);

    paySdk.pay(params);
  }

  getGateway(payment, isMobile, openid) {
    let gateway = '';
    switch (payment) {
      case 'wechat':
        if (openid > 0) {
          gateway = 'WechatPay_Js';
        } else if(isMobile){
	        gateway = 'WechatPay_MWeb';
        }else {
          gateway = 'WechatPay_Native';
        }
        break;

      case 'alipay':
        if (isMobile) {
          gateway = 'Alipay_LegacyWap';
        } else {
          gateway = 'Alipay_LegacyExpress';
        }
        break;

      case 'lianlianpay':
        if (isMobile) {
          gateway = 'Lianlian_Wap';
        } else {
          gateway = 'Lianlian_Web';
        }
        break;
    }

    return gateway;
  }

}
