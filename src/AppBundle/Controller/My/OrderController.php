<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\Order\OrderException;
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

            return ['startTime' => strtotime($fields['startTime']), 'endTime' => (strtotime($fields['endTime']) + 24 * 3600)];
        }

        return ['startTime' => strtotime(date('Y-m', time())), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600))];
    }

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $status = $request->get('display_status');
        $keyWord = $request->get('q');
        $payWays = $request->get('payWays');

        $conditions = [
            'user_id' => $user['id'],
        ];
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
            ['created_time' => 'DESC'],
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

        $goodsSpecs = $this->getGoodsService()->findGoodsSpecsByIds(ArrayToolkit::column($orderItems, 'target_id'));
        $goodsSpecs = ArrayToolkit::index($goodsSpecs, 'id');
        foreach ($orders as &$order) {
            $order['item'] = empty($orderItems[$order['id']]) ? [] : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? [] : $paymentTrades[$order['sn']];
            $order['refund'] = empty($orderRefunds[$order['id']]) ? [] : $orderRefunds[$order['id']];
            $order['goodsId'] = empty($goodsSpecs[$order['item']['target_id']]) ? '' : $goodsSpecs[$order['item']['target_id']]['goodsId'];
        }

        return $this->render('my-order/order/index.html.twig', [
            'orders' => $orders,
            'paginator' => $paginator,
            'request' => $request,
        ]);
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

        return $this->render('my-order/detail-modal.html.twig', [
            'order' => $order,
            'user' => $user,
            'orderItems' => $orderItems,
            'paymentTrade' => $paymentTrade,
            'orderLogs' => $orderLogs,
            'orderDeducts' => $orderDeducts,
            'users' => $users,
        ]);
    }

    public function cancelAction(Request $request, $id)
    {
        $this->tryManageOrder($id);
        $order = $this->getWorkflowService()->close($id, ['type' => 'manual']);

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
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
