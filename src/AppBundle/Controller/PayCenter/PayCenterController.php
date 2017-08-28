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

        $trade = array(
            'goods_title' => $order['title'],
            'order_sn' => $order['sn'],
            'amount' => $order['pay_amount'],
            'pay_type' => 'Native',
            'platform' => $request->request->get('payment'),
            'user_id' => $order['user_id'],
        );

        $result = $this->getPayService()->createTrade($trade);

        var_dump($trade);
        exit;
    }

    public function wechatAction(Request $request)
    {
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
