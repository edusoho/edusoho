<?php

namespace AppBundle\Controller\PayCenter;

use AppBundle\Controller\BaseController;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayCenterController extends BaseController
{
    public function showAction(Request $request)
    {
        $sn = $request->query->get('sn');

        $order = $this->getOrderService()->getOrderBySn($sn);

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

    public function wechatAction(Request $request, $sn)
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
            'notify_url' => '',
            'coin_amount' => 0,
            'create_ip' => '127.0.0.1',
            'price_type' => 'money',
            'attach' => array(
                'user_id' => $order['user_id']
            ),
        );


        $result = $this->getPayService()->createTrade($trade);

        var_dump($result);
        exit;
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
