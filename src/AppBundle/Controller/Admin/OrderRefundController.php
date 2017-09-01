<?php

namespace AppBundle\Controller\Admin;

use Biz\Order\OrderRefundProcessor\OrderRefundProcessorFactory;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;

class OrderRefundController extends BaseController
{
    public function refundsAction(Request $request, $targetType)
    {
        //$conditions = $this->prepareRefundSearchConditions($request->query->all());
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getBizOrderRefundService()->countRefunds($conditions),
            20
        );

        $refunds = $this->getBizOrderRefundService()->searchRefunds(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($refunds, 'order_id');

        $userIds = array_merge(ArrayToolkit::column($refunds, 'created_user_id'), ArrayToolkit::column($refunds, 'deal_user_id'));
        $users = $this->getUserService()->findUsersByIds($userIds);
        
        $orders = $this->getOrderService()->findOrdersByIds($orderIds);
        $orders = ArrayToolkit::index($orders, 'id');

        $orderItems = $this->getOrderService()->findOrderItemsByorderIds($orderIds);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        return $this->render("admin/order-refund/refund-{$targetType}.html.twig", array(
            'refunds' => $refunds,
            'orderItems' => $orderItems,
            'users' => $users,
            'orders' => $orders,
            'paginator' => $paginator,
            'targetType' => $targetType,
        ));
    }

    protected function prepareRefundSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (!empty($conditions['orderSn'])) {
            $order = $this->getOrderService()->getOrderBySn($conditions['orderSn']);
            $conditions['orderId'] = $order ? $order['id'] : -1;
            unset($conditions['orderSn']);
        }

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function auditRefundAction(Request $request, $refundId)
    {
        $refund = $this->getBizOrderRefundService()->getById($refundId);
        $order = $this->getOrderService()->getOrder($refund['order_id']);

        $trade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        if ($request->getMethod() == 'POST') {
            $pass = $request->request->get('result');
            $fileds = $request->request->all();
            $refundData = array('deal_reason' => $request->request->get('note'));

            if ('pass' === $pass) {
                $product = $this->getOrderRefundService()->adoptRefund($refund['order_id'], $refundData);
            } else {
                $product = $this->getOrderRefundService()->refuseRefund($refund['order_id'], $refundData);
            }
            $this->sendAuditRefundNotification($product, $order, $fileds);
            $this->setFlashMessage('success', 'admin.order_refund_handle.success');

            return $this->redirect($this->generateUrl('admin_order_refunds', array('targetType' => $product->targetType)));
        }

        return $this->render('admin/order-refund/refund-confirm-modal.html.twig', array(
            'refund' => $refund,
            'trade' => $trade,
        ));
    }

    protected function sendAuditRefundNotification($product, $order, $data)
    {
        if (isset($data['result']) &&  $data['result']== 'pass') {
            $message = $this->setting('refund.successNotification');
        } else {
            $message = $this->setting('refund.failedNotification');
        }
        if (empty($message)) {
            return false;
        }

        $backUrl = $product->backUrl;
        $targetUrl = $this->generateUrl($backUrl['routing'], $backUrl['params']);
        $variables = array(
            'item' => "<a href='{$targetUrl}'>".$product->title."</a>",
            'amount' => $order['pay_amount'],
            'note' => $data['note'],
        );

        $message = StringToolkit::template($message, $variables);
        $this->getNotificationService()->notify($order['created_user_id'], 'default', $message);
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getOrderRefundService()
    {
        return $this->createService('OrderRefund:OrderRefundService');
    }

    protected function getBizOrderRefundService()
    {
        return $this->createService('Order:OrderRefundService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
