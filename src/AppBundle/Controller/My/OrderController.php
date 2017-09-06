<?php

namespace AppBundle\Controller\My;

use Biz\Course\Service\CourseOrderService;
use Biz\Order\OrderRefundProcessor\OrderRefundProcessorFactory;
use Biz\Order\Service\OrderService;
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
        $status = $request->get('status');
        $keyWord = $request->get('q');
        $payWays = $request->get('payWays');

        $conditions = array(
            'user_id' => $user['id'],
        );
        if (!empty($status)) {
            $conditions['status'] = $status;
        }

        if (!empty($keyWord)) {
            $conditions['order_item_title'] = $keyWord;
        }

        if (!empty($payWays)) {
            $conditions['payment'] = $payWays;
        }

        $conditions['start_time'] = 0;
        $conditions['end_time'] = time();
        switch ($request->get('lastHowManyMonths')) {
            case 'oneWeek':
                $conditions['start_time'] = $conditions['end_time'] - 7 * 24 * 3600;
                break;
            case 'twoWeeks':
                $conditions['start_time'] = $conditions['end_time'] - 14 * 24 * 3600;
                break;
            case 'oneMonth':
                $conditions['start_time'] = $conditions['end_time'] - 30 * 24 * 3600;
                break;
            case 'twoMonths':
                $conditions['start_time'] = $conditions['end_time'] - 60 * 24 * 3600;
                break;
            case 'threeMonths':
                $conditions['start_time'] = $conditions['end_time'] - 90 * 24 * 3600;
                break;
        }


        var_dump($conditions);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            20
        );

        $createdOrderCount = $this->getOrderService()->countOrders(array('userId' => $user['id'], 'status' => 'created'));
        $refundingOrderCount = $this->getOrderService()->countOrders(array('userId' => $user['id'], 'status' => 'refunding'));
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

        foreach ($orders as &$order) {
            //@TODO： orderItem和Order不是一一对应的，这个要在产品上做改变
            $order['item'] = empty($orderItems[$order['id']]) ? array() : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? array() : $paymentTrades[$order['sn']];
        }

        $waitToBePaidCountConditions = array('userId' => $user['id'], 'status' => 'created');
        $waitToBePaidCount = $this->getOrderService()->countOrders($waitToBePaidCountConditions);
        //
//        foreach ($orders as $index => $expiredOrderToBeUpdated) {
//            if ((($expiredOrderToBeUpdated['createdTime'] + 48 * 60 * 60) < time()) && ($expiredOrderToBeUpdated['status'] == 'created')) {
//                $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
//                $orders[$index]['status'] = 'cancelled';
//                $waitToBePaidCount -= 1;
//            }
//        }

        return $this->render('my-order/index.html.twig', array(
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

        $user = $this->getUserService()->getUser($order['user_id']);

        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($order['id']);

        //orderItem和order的对应关系不是一对一，所以这里会有问题
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'user_id'));

        return $this->render('my-order/detail-modal.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderItems' => $orderItems,
            'paymentTrade' => $paymentTrade,
            'orderLogs' => $orderLogs,
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
        $order = $this->getOrderService()->cancelOrder($id, '取消订单');

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
