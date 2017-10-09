<?php

namespace AppBundle\Controller\Cashier;

use ApiBundle\Api\ApiRequest;
use AppBundle\Component\Payment\Wxpay\JsApiPay;
use Symfony\Component\HttpFoundation\Request;

class WechatController extends PaymentController
{
    public function getOpenIdAction(Request $request)
    {
        if ($request->query->get('s')) {
            $request->getSession()->set('wechat_pay_params', $request->query->all());
        }

        $user = $this->getUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，支付失败。');
        }

        $biz = $this->getBiz();

        $options = $biz['payment.platforms.options']['wechat'];
        $jsApi = new JsApiPay(array(
            'appid' => $options['appid'],
            'account' => $options['mch_id'],
            'key' => $options['key'],
            'secret' => $options['secret'],
            'redirect_uri' => $this->generateUrl('cashier_wechat_pay_get_openid', array(), true),
            'isMicroMessenger' => true,
        ), $request);

        $openid = $jsApi->getOpenid();

        return $this->forward('AppBundle:Cashier/Wechat:payInWechat', array(
            'openId' => $openid,
            'params' => $request->getSession()->get('wechat_pay_params')
        ));
    }

    public function payInWechatAction($openId, $params)
    {
        file_put_contents('log.text', $openId);
        $apiKernel = $this->get('api_resource_kernel');
        $apiRequest = new ApiRequest(
            '/api/trades',
            'POST',
            array(),
            array(
                'gateway' => 'WechatPay_Js',
                'type' => 'purchase',
                'openId' => $openId,
                'orderSn' => $params['orderSn'],
                'coinAmount' => $params['coinAmount'],
            ),
            array()
        );

        $result = $apiKernel->handleApiRequest($apiRequest);

        return $this->render(
            'cashier/wechat/h5.html.twig', array(
            'trade' => $result
        ));
    }

    public function returnAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $this->getPayService()->queryTradeFromPlatform($tradeSn);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $tradeSn)));
    }

    public function notifyAction(Request $request, $payment)
    {
        $result = $this->getPayService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse($result);
    }
}
