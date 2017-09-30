<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Exception\OrderPayCheckException;
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

        if (!$order || $this->getUser()->getId() !== $order['user_id']) {
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

        return $this->render(
            'cashier/show.html.twig', array(
            'order' => $order,
            'payments' => $payments,
        ));
    }

    public function redirectAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->getTradeByTradeSn($tradeSn);

        if ($trade['user_id'] !== $this->getCurrentUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->redirect($trade['platform_created_result']['url']);
    }

    public function isPaidCheckAction(Request $request)
    {
        $orderSn = $request->query->get('orderSn');
        $order = $this->getOrderService()->getOrderBySn($orderSn);
        $trade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);
        return $this->createJsonResponse(array(
            'isPaid' => $trade['status'] == 'paid',
            'redirectUrl' => $this->generateUrl('cashier_pay_success', array('trade_sn' => $trade['trade_sn']))
        ));
    }

    public function createTradeAction(Request $request)
    {
        $payment = $request->request->get('payment');
        $payment = preg_replace('/\.\w+/', '', $payment);
        $payment = ucfirst($payment);

        return $this->forward("AppBundle:Cashier/{$payment}:createTrade");
    }

    public function payAction(Request $request)
    {
        $sn = $request->request->get('sn');

        $payment = $request->request->get('payment');
        $payment = preg_replace('/\.\w+/', '', $payment);
        $payment = ucfirst($payment);

        $order = $this->getOrderService()->getOrderBySn($sn);
        if ($order['status'] == 'success'
            || $order['status'] == 'paid'
            || $order['status'] == 'fail') {
            return $this->createJsonResponse(array(
                'isPaid' => 1,
                'redirectUrl' => $this->generateUrl('cashier_pay_success', array('trade_sn' => $order['trade_sn']))
            ));
        }

        try {
            $params = $request->request->all();
            $params['clientIp'] = $request->getClientIp();
            $trade = $this->getOrderFacadeService()->payingOrder($sn, $request->request->all());
        } catch (OrderPayCheckException $e) {
            return $this->createMessageResponse('error', $this->trans($e->getMessage()));
        }

        $action = $this->isMobileClient() ? 'mobilePay' : 'pcPay';

        return $this->forward("AppBundle:Cashier/{$payment}:{$action}", array('trade' => $trade));
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
            'data' => $this->get('web.twig.order_extension')->majorCurrency($priceAmount),
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
