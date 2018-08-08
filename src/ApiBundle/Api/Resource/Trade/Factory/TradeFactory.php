<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TradeFactory
{
    public function create($gateway)
    {
        $tradeInstance = null;
        switch ($gateway) {
            case 'WechatPay_Native':
                $tradeInstance = new WechatPayNativeTrade();
                break;
            case 'WechatPay_MWeb':
                $tradeInstance = new WeChatPayMWebTrade();
                break;
            case 'WechatPay_Js':
                $tradeInstance = new WechatPayJsTrade();
                break;
            case 'WechatPay_App':
                $tradeInstance = new WechatPayAppTrade();
                break;
            case 'Alipay_LegacyExpress':
                $tradeInstance = new AlipayLegacyExpressTrade();
                break;
            case 'Alipay_LegacyWap':
                $tradeInstance = new AlipayLegacyWapTrade();
                break;
            case 'Lianlian_Web':
                $tradeInstance = new LianlianPayWebTrade();
                break;
            case 'Lianlian_Wap':
                $tradeInstance = new LianlianPayWapTrade();
                break;
            case 'WeChatPay_MiniApp':
                $tradeInstance = new WeChatPayMiniAppTrade();
                break;
            case 'Coin':
                $tradeInstance = new CoinTrade();
                break;
            case 'WechatPay_H5':
                $tradeInstance = new WeChatPayH5Trade();
                break;
            case 'WechatPay_JsH5':
                $tradeInstance = new WeChatPayJsH5Trade();
                break;
            case 'Alipay_LegacyH5':
                $tradeInstance = new AlipayLegacyH5Trade();
                // no break
            default:
                throw new BadRequestHttpException();
        }

        return $tradeInstance;
    }
}
