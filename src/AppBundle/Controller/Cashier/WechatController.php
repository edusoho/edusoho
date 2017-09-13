<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Component\Payment\Wxpay\JsApiPay;
use AppBundle\Controller\BaseController;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Omnipay\WechatPay\Helper;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\MathToolkit;

class WechatController extends BaseController
{
    public function payAction($trade)
    {
        if ($this->getWebExtension()->isMicroMessenger()) {
            $this->get('session')->set('trade_info', $trade);
            return $this->redirect($this->generateUrl('cashier_wechat_h5_pay'));
        } else {
            return $this->forward('AppBundle:Cashier/Wechat:qrcode', array('trade' => $trade));
        }
    }

    public function qrcodeAction($trade)
    {
        $trade['platform_type'] = 'Native';
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'wechat'), true);
        $result = $this->getPayService()->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])));
        }

        if ($result['platform_created_result']['return_code'] == 'SUCCESS') {
            $result = MathToolkit::multiply(
                $result,
                array('cash_amount'),
                0.01
            );

            return $this->render(
                'cashier/wechat/qrcode.html.twig', array(
                'trade' => $result,
            ));
        }

        return $this->createMessageResponse('warning', $result['platform_created_result']['return_msg'], '微信支付设置错误');
    }

    public function h5Action()
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，支付失败。');
        }

        $biz = $this->getBiz();

        $request = $this->get('request_stack')->getMasterRequest();

        $options = $biz['payment.platforms.options']['wechat'];
        $jsApi = new JsApiPay(array(
            'appid' => $options['appid'],
            'account' => $options['mch_id'],
            'key' => $options['key'],
            'secret' => $options['secret'],
            'redirect_uri' => $this->generateUrl('cashier_wechat_h5_pay', array(), true),
            'isMicroMessenger' => true,
        ), $request);

        $openid = $jsApi->getOpenid();

        $trade = $this->get('session')->get('trade_info');

        if ($user['id'] != $trade['user_id']) {
            return $this->createMessageResponse('error', '不是您创建的订单，支付失败');
        }

        $trade['open_id'] = $openid;
        $trade['platform_type'] = 'Js';
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'wechat'), true);
        $result = $this->getPayService()->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])));
        }

        $result = MathToolkit::multiply(
            $result,
            array('cash_amount'),
            0.01
        );

        return $this->render(
            'cashier/wechat/h5.html.twig', array(
            'trade' => $result,
            'jsApiParameters' => json_encode($result['platform_created_result']),
        ));
    }

    public function h5ReturnAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->queryTradeFromPlatform($tradeSn);

        if ($trade['trade_state'] === 'SUCCESS') {
            $this->getPayService()->notifyPaid('wechat', Helper::array2xml($trade));
        }

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $tradeSn)));
    }

    public function rollAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->queryTradeFromPlatform($tradeSn);

        if ($trade['trade_state'] === 'SUCCESS') {
            $this->getPayService()->notifyPaid('wechat', Helper::array2xml($trade));

            return $this->createJsonResponse(true);
        } else {
            return $this->createJsonResponse(false);
        }
    }

    public function notifyAction(Request $request, $payment)
    {
        $result = $this->getPayService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse($result);
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
