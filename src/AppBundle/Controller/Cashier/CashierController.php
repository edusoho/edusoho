<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Order\Service\OrderService;
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

        try {
            $order = $this->getOrderFacadeService()->checkOrderBeforePay($sn);
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $this->trans($e->getMessage()));
        }

        $this->getOrderService()->setOrderPaying($order['id'], array());

        $payment = $request->request->get('payment');
        $payment = ucfirst($payment);

        return $this->forward("AppBundle:Cashier/{$payment}:pay", array('sn' => $order['sn']));
    }

    public function successAction(Request $request)
    {
        $sn = $request->query->get('sn');
        $order = $this->getOrderService()->getOrderBySn($sn);

        $items = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $item1 = reset($items);

        $biz = $this->getBiz();

        /* @var $product Product */
        $product = $biz['order.product.'.$item1['target_type']];

        $product->init(array('targetId' => $item1['target_id']));

        return $this->render('cashier/success.html.twig', array(
            'goto' => $this->generateUrl($product->successUrl[0], $product->successUrl[1]),
        ));
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
