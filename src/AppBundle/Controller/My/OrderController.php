<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\Order\OrderException;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\OrderFacade\Service\OrderRefundService as LocalOrderRefundService;
use Codeages\Biz\Order\Service\OrderRefundService;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Order\Service\WorkflowService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends BaseController
{
    protected function getTimeRange($fields)
    {
        if (isset($fields['startTime']) && isset($fields['endTime']) && '' != $fields['startTime'] && '' != $fields['endTime']) {
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

        $orderRefunds = $this->getOrderRefundService()->findRefundsByOrderIds($orderIds);
        $orderRefunds = ArrayToolkit::index($orderRefunds, 'order_id');

        foreach ($orders as &$order) {
            $order['item'] = empty($orderItems[$order['id']]) ? array() : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? array() : $paymentTrades[$order['sn']];
            $order['refund'] = empty($orderRefunds[$order['id']]) ? array() : $orderRefunds[$order['id']];

            $order['product'] = empty($order['item']) ? null : $this->getOrderFacadeService()->getOrderProductByOrderItem($order['item']);
        }

        return $this->render('my-order/order/index.html.twig', array(
            'orders' => $orders,
            'paginator' => $paginator,
            'request' => $request,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $order = $this->tryManageOrder($id);

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
            $this->createNewException(OrderException::BEYOND_AUTHORITY());
        }

        return $order;
    }

    /**
     * @return OrderService
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
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->createService('Order:OrderRefundService');
    }

    /**
     * @return LocalOrderRefundService
     */
    protected function getLocalOrderRefundService()
    {
        return $this->createService('OrderFacade:OrderRefundService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
