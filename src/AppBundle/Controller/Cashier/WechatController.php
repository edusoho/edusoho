<?php

namespace AppBundle\Controller\Cashier;

use ApiBundle\Api\ApiRequest;
use AppBundle\Component\Payment\Wxpay\JsApiPay;
use Symfony\Component\HttpFoundation\Request;

class WechatController extends PaymentController
{
    public function wechatJsPayAction(Request $request)
    {
        $params = $request->query->all();

        $user = $this->getUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，支付失败。');
        }

        try {
            $biz = $this->getBiz();

            $options = $biz['payment.platforms.options']['wechat'];

            $jsApi = new JsApiPay(array(
                'appid' => $options['appid'],
                'account' => $options['mch_id'],
                'key' => $options['key'],
                'secret' => $options['secret'],
                'redirect_uri' => $this->generateUrl('cashier_wechat_js_pay', array(), true),
                'isMicroMessenger' => true,
            ), $request);

            $openid = $jsApi->getOpenid();
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', '不能使用微信支付，可能是网校未开启微信支付或配置不正确');
        }

        $apiKernel = $this->get('api_resource_kernel');
        $apiRequest = new ApiRequest(
            '/api/trades',
            'POST',
            array(),
            array(
                'gateway' => 'WechatPay_Js',
                'type' => empty($params['type']) ? 'purchase' : $params['type'],
                'openid' => $openid,
                'orderSn' => $params['orderSn'],
                'coinAmount' => empty($params['coinAmount']) ? 0 : $params['coinAmount'],
                'payPassword' => empty($params['payPassword']) ? '' : $params['payPassword'],
            ),
            array()
        );

        $result = $apiKernel->handleApiRequest($apiRequest);
        if (!empty($result['paidSuccessUrl'])) {
            return $this->redirect($result['paidSuccessUrl']);
        }

        $trade = $this->getPayService()->queryTradeFromPlatform($result['tradeSn']);

        return $this->render(
            'cashier/wechat/h5.html.twig',
            array(
                'trade' => $trade,
            )
        );
    }

    public function wechatAppMwebTradeAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->getTradeByTradeSn($tradeSn);

        if ('created' == $trade['status'] || 'paying' == $trade['status']) {
            $platformCreatedResult = $this->getPayService()->getCreateTradeResultByTradeSnFromPlatform($tradeSn);

            return $this->render(
                'cashier/wechat/app-redirect.html.twig',
                array(
                    'mwebUrl' => $platformCreatedResult['mweb_url'],
                    'trade' => $trade,
                )
            );
        }

        return $this->render('cashier/wechat/app-result.html.twig',
            array(
                'trade' => $trade,
            )
        );
    }

    public function returnAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->queryTradeFromPlatform($tradeSn);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $trade['trade_sn'])));
    }

    public function notifyAction(Request $request, $payment)
    {
        $result = $this->getPayService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse($result);
    }
}
