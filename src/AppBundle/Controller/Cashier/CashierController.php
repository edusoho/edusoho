<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Order\Status\Order\CreatedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\SuccessOrderStatus;
use Codeages\Biz\Framework\Pay\Service\AccountService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Codeages\Biz\Framework\Pay\Status\PayingStatus;
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

        if ($order['status'] == SuccessOrderStatus::NAME) {
            return $this->forward('AppBundle:Cashier/Cashier:purchaseSuccess', array('trade' => array(
                'order_sn' => $order['sn'],
            )));
        }

        if (!in_array($order['status'], array(CreatedOrderStatus::NAME, PayingStatus::NAME))) {
            return $this->createMessageResponse('info', $this->trans('cashier.order.status.changed_tips'));
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
        $payment = substr($payment, 0, stripos($payment, '.'));
        $payment = ucfirst($payment);

        $trade = $this->makeTrade($order, $request);

        $this->getWorkflowService()->paying($order['id'], array());

        return $this->forward("AppBundle:Cashier/{$payment}:pay", array('trade' => $trade));
    }

    private function makeTrade($order, Request $request)
    {
        $coinAmount = $request->request->get('coinAmount');
        $trade = array(
            'goods_title' => $order['title'],
            'goods_detail' => '',
            'order_sn' => $order['sn'],
            'amount' => $order['pay_amount'],
            'platform' => $request->request->get('payment'),
            'user_id' => $order['user_id'],
            'coin_amount' => $coinAmount * 100,
            'cash_amount' => $this->getOrderFacadeService()->getTradePayCashAmount($order, $coinAmount) * 100,
            'create_ip' => $request->getClientIp(),
            'price_type' => 'money',
            'type' => 'purchase',
            'attach' => array(
                'user_id' => $order['user_id'],
            ),
        );

        return $trade;
    }

    public function successAction(Request $request)
    {
        $tradeSn = $request->query->get('trade_sn');
        $trade = $this->getPayService()->getTradeByTradeSn($tradeSn);

        return $this->forward("AppBundle:Cashier/Cashier:{$trade['type']}Success", array(
            'trade' => $trade,
        ));
    }

    public function rechargeSuccessAction($trade)
    {
        return $this->render('cashier/success.html.twig', array(
            'goto' => $this->generateUrl('my_coin'),
        ));
    }

    public function purchaseSuccessAction($trade)
    {
        $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);

        $items = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $item1 = reset($items);

        $params = array(
            'targetId' => $item1['target_id'],
            'num' => $item1['num'],
            'unit' => $item1['unit'],
        );
        $product = $this->getOrderFacadeService()->getOrderProduct($item1['target_type'], $params);

        return $this->render('cashier/success.html.twig', array(
            'goto' => $this->generateUrl($product->successUrl[0], $product->successUrl[1]),
        ));
    }

    public function priceAction(Request $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);
        $coinAmount = $request->request->get('coinAmount');
        $priceAmount = $this->getOrderFacadeService()->getTradePayCashAmount(
            $order,
            $coinAmount
        );

        return $this->createJsonResponse(array(
            'data' => $this->get('web.twig.app_extension')->majorCurrency($priceAmount),
        ));
    }

    public function checkPayPasswordAction(Request $request)
    {
        $password = $request->query->get('value');

        $isRight = $this->getAccountService()->validatePayPassword($this->getUser()->getId(), $password);

        if (!$isRight) {
            $response = array('success' => false, 'message' => '支付密码不正确');
        } else {
            $response = array('success' => true, 'message' => '支付密码正确');
        }

        return $this->createJsonResponse($response);
    }

    /**
     * @return AccountService
     */
    public function getAccountService()
    {
        return $this->createService('Pay:AccountService');
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

    private function getWorkflowService()
    {
        return $this->createService('Order:WorkflowService');
    }
}
