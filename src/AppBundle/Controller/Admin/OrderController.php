<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\MathToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Order\Service\OrderService;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\OrderToolkit;

class OrderController extends BaseController
{
    public function manageAction(Request $request)
    {
        $conditions = $request->query->all();

        $conditions = $this->prepareConditions($conditions);

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

        foreach ($orders as &$order) {
            $order['item'] = empty($orderItems[$order['id']]) ? array() : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? array() : $paymentTrades[$order['sn']];
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'user_id'));

        return $this->render(
            'admin/order/list.html.twig',
            array(
                'request' => $request,
                'orders' => $orders,
                'users' => $users,
                'paginator' => $paginator,
            )
        );
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['orderItemType'])) {
            $conditions['order_item_target_type'] = $conditions['orderItemType'];
        }

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['start_time'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['end_time'] = strtotime($conditions['endDateTime']);
        }

        if (isset($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }

        if (!empty($conditions['displayStatus'])) {
            $conditions['statuses'] = $this->container->get('web.twig.order_extension')->getOrderStatusFromDisplayStatus($conditions['displayStatus'], 1);
        }

        return $conditions;
    }

    public function detailAction($id)
    {
        $order = $this->getOrderService()->getOrder($id);

        $user = $this->getUserService()->getUser($order['user_id']);

        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($order['id']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $orderDeducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);

        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'user_id'));

        $orderLogs = OrderToolkit::removeUnneededLogs($orderLogs);

        return $this->render('admin/order/detail.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderLogs' => $orderLogs,
            'orderItems' => $orderItems,
            'orderDeducts' => $orderDeducts,
            'paymentTrade' => $paymentTrade,
            'users' => $users,
        ));
    }

    public function adjustPriceAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $this->getOrderFacadeService()->adjustOrderPrice(
                $id, MathToolkit::simple($request->request->get('adjustPrice'), 100)
            );

            return $this->createJsonResponse(array());
        }

        $order = $this->getOrderService()->getOrder($id);
        $adjustInfo = $this->getOrderFacadeService()->getOrderAdjustInfo($order);

        return $this->render('admin/order/adjust-price-modal.html.twig', array(
            'order' => $order,
            'adjustInfo' => $adjustInfo,
        ));
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return \Codeages\Biz\Order\Service\OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
