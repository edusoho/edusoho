<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Common\MathToolkit;

class CashierController extends BaseController
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

        return $this->render('cashier/show.html.twig', array(
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
        $payment = ucfirst($payment);
        return $this->forward("AppBundle:Cashier/{$payment}:pay", array('sn' => $order['sn']));
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
