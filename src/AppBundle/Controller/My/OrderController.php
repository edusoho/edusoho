<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\MathToolkit;
use Biz\Course\Service\CourseOrderService;
use Biz\Order\OrderRefundProcessor\OrderRefundProcessorFactory;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Order\Service\WorkflowService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;

class OrderController extends BaseController
{
    protected function getTimeRange($fields)
    {
        if (isset($fields['startTime']) && isset($fields['endTime']) && $fields['startTime'] != '' && $fields['endTime'] != '') {
            if ($fields['startTime'] > $fields['endTime']) {
                return false;
            }

            return array('startTime' => strtotime($fields['startTime']), 'endTime' => (strtotime($fields['endTime']) + 24 * 3600));
        }

        return array('startTime' => strtotime(date('Y-m', time())), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600)));
    }

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $status = $request->get('display_status');
        $keyWord = $request->get('q');
        $payWays = $request->get('payWays');

        $conditions = array(
            'user_id' => $user['id'],
        );
        if (!empty($status)) {
            $conditions['display_status'] = $status;
        }

        if (!empty($keyWord)) {
            $conditions['order_item_title'] = $keyWord;
        }

        if (!empty($payWays)) {
            $conditions['payment'] = $payWays;
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            20
        );

        $createdOrderCount = $this->getOrderService()->countOrders(array('user_id' => $user['id'], 'display_status' => 'no_paid'));
        $refundingOrderCount = $this->getOrderService()->countOrders(array('user_id' => $user['id'], 'display_status' => 'refunding'));
        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($orders, 'id');
        $orderSns = ArrayToolkit::column($orders, 'sn');
        $orderItems = $this->getOrderService()->findOrderItemsByOrderIds($orderIds);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        $paymentTrades = $this->getPayService()->findTradesByOrderSns($orderSns);
        $paymentTrades = ArrayToolkit::index($paymentTrades, 'order_sn');

        $orderRefunds = $this->

        foreach ($orders as &$order) {
            $order['item'] = empty($orderItems[$order['id']]) ? array() : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? array() : $paymentTrades[$order['sn']];
            $order = MathToolkit::multiply($order, array('price_amount', 'pay_amount'), 0.01);
        }

        return $this->render('my-order/order/index.html.twig', array(
            'orders' => $orders,
            'paginator' => $paginator,
            'request' => $request,
            'createdOrderCount' => $createdOrderCount,
            'refundingOrderCount' => $refundingOrderCount,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $order = $this->tryManageOrder($id);
        $order = MathToolkit::multiply($order, array('price_amount', 'pay_amount'), 0.01);

        $user = $this->getUserService()->getUser($order['user_id']);

        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($order['id']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        $orderDeducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'user_id'));

        return $this->render('my-order/detail-modal.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderItems' => $orderItems,
            'paymentTrade' => $paymentTrade,
            'orderLogs' => $orderLogs,
            'orderDeducts' => $orderDeducts,
            'users' => $users,
        ));
    }

    public function cancelRefundAction(Request $request, $id)
    {
        $order = $this->tryManageOrder($id);
        $processor = OrderRefundProcessorFactory::create($order['targetType']);
        $processor->cancelRefundOrder($id);

        return $this->createJsonResponse(true);
    }

    public function cancelAction(Request $request, $id)
    {
        $this->tryManageOrder($id);
        $order = $this->getWorkflowService()->close($id, array('type' => 'manual'));

        return $this->createJsonResponse(true);
    }

    protected function tryManageOrder($id)
    {
        $currentUser = $this->getCurrentUser();
        $order = $this->getOrderService()->getOrder($id);

        if ($currentUser['id'] != $order['user_id']) {
            throw $this->createAccessDeniedException('该订单不属于当前登录用户');
        }

        return $order;
    }

    /**
     * @return \Codeages\Biz\Framework\Order\Service\OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return WorkflowService
     */
    protected function getWorkflowService()
    {
        return $this->createService('Order:WorkflowService');
    }

    /**
     * @return CourseOrderService
     */
    protected function getCourseOrderService()
    {
        return $this->getBiz()->service('Course:CourseOrderService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
