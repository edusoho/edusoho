import WechatPayNative from './wechatpay_native';
import AlipayLegacyExpress from './alipay_legacy_express';
import AlipayLegacyWap from './alipay_legacy_wap';
import LianlianpayWap from './lianlianpay_wap';
import LianlianpayWeb from './lianlianpay_web';
import WechatPayJs from './wechatpay_js';

export default class PaySDK {

  pay(params) {
    console.log(params);
    let gateway = this.getGateway(params['payment'], params['isMobile'], params['isWechat']);
    params.gateway = gateway;
    let paySdk = null;
    switch (gateway) {
      case 'WechatPay_Native':
        paySdk = this.wpn ? this.wpn : this.wpn = new WechatPayNative();
        break;
      case 'WechatPay_Js':
        paySdk = this.wpj ? this.wpj : this.wpj = new WechatPayJs();
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

    paySdk.pay(params);
  }

  getGateway(payment, isMobile, isWechat) {

    let gateway = '';
    switch (payment) {
      case 'wechat':
        if (isWechat) {
          gateway = 'WechatPay_Js';
        } else {
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
