<?php

namespace AppBundle\Controller\Cashier;

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
        $trade['pay_type'] = 'Native';
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'wechat'));
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
