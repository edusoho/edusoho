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
            $order = $this->getOrderFacadeService()->checkOrderBeforePay($sn, $request->request->all());
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $this->trans($e->getMessage()));
        }

        $payment = $request->request->get('payment');
        $payment = ucfirst($payment);

        $trade = $this->makeTrade($order, $request);
        $this->getOrderService()->setOrderPaying($order['id'], array());
        return $this->forward("AppBundle:Cashier/{$payment}:pay", array('trade' => $trade));
    }

    private function makeTrade($order, Request $request)
    {
        $coinAmount = $request->request->get('coinAmount');
        $cashAmount = $order['pay_amount'];
        $trade = array(
            'goods_title' => $order['title'],
            'goods_detail' => '',
            'order_sn' => $order['sn'],
            'amount' => $this->getOrderFacadeService()->getTradeShouldPayAmount($order, $coinAmount) * 100,
            'platform' => $request->request->get('payment'),
            'user_id' => $order['user_id'],
            'coin_amount' => $coinAmount,
            'cash_amount' => $cashAmount,
            'create_ip' => $request->getClientIp(),
            'price_type' => 'money',
            'attach' => array(
                'user_id' => $order['user_id'],
            ),
        );

        return $trade;
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

    public function priceAction(Request $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);
        $coinAmount = $request->request->get('coinAmount');
        $priceAmount = $this->getOrderFacadeService()->getTradeShouldPayAmount(
            $order,
            $coinAmount
        );

        return $this->createJsonResponse(array(
            'data' => $this->get('web.twig.app_extension')->majorCurrency($priceAmount)
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
