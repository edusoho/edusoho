<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Common\MathToolkit;
use AppBundle\Controller\BaseController;
use Biz\Order\OrderException;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\System\Service\SettingService;
use Biz\User\UserException;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Order\Status\Order\CreatedOrderStatus;
use Codeages\Biz\Pay\Service\AccountService;
use Codeages\Biz\Pay\Service\PayService;
use Codeages\Biz\Pay\Status\PayingStatus;
use Symfony\Component\HttpFoundation\Request;

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
            $this->createNewException(OrderException::NOTFOUND_ORDER());
        }

        if ($this->getOrderFacadeService()->isOrderPaid($order['id'])) {
            return $this->forward('AppBundle:Cashier/Cashier:purchaseSuccess', array('trade' => array(
                'order_sn' => $order['sn'],
            )));
        }

        if (!in_array($order['status'], array(CreatedOrderStatus::NAME, PayingStatus::NAME))) {
            return $this->createMessageResponse('info', $this->trans('cashier.order.status.changed_tips'));
        }

        $payments = $this->getPayService()->findEnabledPayments();

        return $this->render(
            'cashier/show.html.twig',
            array(
                'order' => $order,
                'product' => $this->getProduct($order['id']),
                'payments' => $payments,
            )
        );
    }

    private function getProduct($orderId)
    {
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($orderId);
        $orderItem = reset($orderItems);

        return $this->getOrderFacadeService()->getOrderProductByOrderItem($orderItem);
    }

    public function redirectAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->getTradeByTradeSn($tradeSn);

        if ($trade['user_id'] !== $this->getCurrentUser()->getId()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        return $this->redirect($trade['platform_created_result']['url']);
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
            'product' => $product,
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
        $user = $this->getCurrentUser();
        $password = $request->query->get('value');
        $rateLimiter = $this->getRateLimiter($user['email'], 5, 300);

        $maxAllowance = $rateLimiter->getAllow($user['email']);

        if (empty($maxAllowance)) {
            $response = array('success' => false, 'message' => '错误次数太多，请5分钟后再试');
            goto end;
        }
        $isRight = $this->getAccountService()->validatePayPassword($this->getUser()->getId(), $password);

        if (!$isRight) {
            $rateLimiter->check($user['email']);
            $response = array('success' => false, 'message' => '支付密码不正确');
        } else {
            $response = array('success' => true, 'message' => '支付密码正确');
        }
        end:
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

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return WeChatService
     */
    private function getWeChatService()
    {
        return $this->createService('WeChat:WeChatService');
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $maxAllowance
     * @param [type] $period
     *
     * @return \Codeages\RateLimiter\RateLimiter
     */
    private function getRateLimiter($name, $maxAllowance, $period)
    {
        $rateLimiter = $this->getBiz()->offsetGet('ratelimiter.factory');

        return $rateLimiter($name, $maxAllowance, $period);
    }
}
