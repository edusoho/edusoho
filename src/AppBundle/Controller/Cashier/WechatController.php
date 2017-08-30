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
    public function payAction($sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);
        $trade = array(
            'goods_title' => $order['title'],
            'goods_detail' => '',
            'order_sn' => $order['sn'],
            'amount' => $order['pay_amount'],
            'pay_type' => 'Native',
            'platform' => 'wechat',
            'user_id' => $order['user_id'],
            'notify_url' => $this->generateUrl('cashier_pay_notify', array('payment' => 'wechat')),
            'coin_amount' => 0,
            'create_ip' => '127.0.0.1',
            'price_type' => 'money',
            'attach' => array(
                'user_id' => $order['user_id'],
            ),
        );

        $result = $this->getPayService()->createTrade($trade);

        if ($result['platform_created_result']['return_code'] == 'SUCCESS') {
            $order = MathToolkit::multiply(
                $order,
                array('price_amount', 'pay_amount'),
                0.01
            );

            return $this->render(
                'cashier/wechat/qrcode.html.twig', array(
                'order' => $order,
                'trade' => $result,
                'qrcodeUrl' => $result['platform_created_result']['code_url'],
            ));
        }

        throw new \RuntimeException($result['platform_created_result']['return_msg']);
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
