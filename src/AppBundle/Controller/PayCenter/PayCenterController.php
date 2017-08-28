<?php

namespace AppBundle\Controller\PayCenter;

use AppBundle\Controller\BaseController;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Common\MathToolkit;

class PayCenterController extends BaseController
{
    public function showAction(Request $request)
    {
        $sn = $request->query->get('sn');

        $order = $this->getOrderService()->getOrderBySn($sn);
        $order = MathToolkit::multiply(
            $order,
            array('price_amount', 'pay_amount'),
            0.01
        );

        if (!$order) {
            throw new NotFoundHttpException();
        }

        $payments = $this->getPayService()->findEnabledPayments();

        return $this->render('pay-center/show.html.twig', array(
            'order' => $order,
            'payments' => $payments,
        ));
    }

    public function payAction(Request $request)
    {
        $sn = $request->request->get('sn');

        $order = $this->getOrderService()->getOrderBySn($sn);

        if (!$order) {
            throw new NotFoundHttpException('order.not.exist');
        }

        $payment = $request->request->get('payment');

        return $this->forward("AppBundle:PayCenter/PayCenter:{$payment}", array('sn' => $order['sn']));
    }

    public function wechatAction($sn)
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
            'notify_url' => $this->generateUrl('wechat_notify'),
            'coin_amount' => 0,
            'create_ip' => '127.0.0.1',
            'price_type' => 'money',
            'attach' => array(
                'user_id' => $order['user_id'],
            ),
        );

        $result = $this->getPayService()->createTrade($trade);

        if ($result['platform_created_result']['return_code'] == 'SUCCESS') {
            return $this->render('pay-center/wxpay-qrcode.html.twig', array(
                'order' => $order,
                'qrcodeUrl' => $result['platform_created_result']['code_url']
            ));
        }

        throw new \RuntimeException($result['platform_created_result']['return_msg']);
    }

    public function wechatNotifyAction(Request $request, $payment)
    {
        $this->getPayService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse(1);
    }

    public function alipayAction(Request $request)
    {
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
