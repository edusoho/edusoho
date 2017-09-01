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
            $pass = $request->request->get('pass', '');

            if ('pass' === $pass) {
                $product = $this->getOrderRefundService()->refuseRefund($refund['order_id']);
            } else {
                $product = $this->getOrderRefundService()->refuseRefund($refund['order_id'], array('deal_reason' => $request->request->get('note')));
                $this->setFlashMessage('success', '拒绝退款');
            }

         
            return $this->redirect($this->generateUrl('admin_order_refunds', array('targetType' => $product->targetType)));
            // if ($order['targetType'] == 'course') {
            //     $this->sendAuditRefundNotification($orderRefundProcessor, $order, $data);
            // } else {
            //     if ($pass) {
            //         $this->getNotificationService()->notify($order['userId'], 'order_refund', array('type' => 'audit_pass'));
            //     } else {
            //         $this->getNotificationService()->notify($order['userId'], 'order_refund', array('type' => 'audit_reject', 'reason' => $data['note']));
            //     }
            // }
        }

        return $this->render('admin/order-refund/refund-confirm-modal.html.twig', array(
            'refund' => $refund,
            'trade' => $trade,
        ));
    }

    protected function sendAuditRefundNotification($orderRefundProcessor, $order, $data)
    {
        $target = $orderRefundProcessor->getTarget($order['targetId']);
        if (empty($target)) {
            return false;
        }

        if ($data['result'] == 'pass') {
            $message = $this->setting('refund.successNotification', '');
        } else {
            $message = $this->setting('refund.failedNotification', '');
        }

        if (empty($message)) {
            return false;
        }

        $targetUrl = $this->generateUrl($order['targetType'].'_show', array('id' => $order['targetId']));
        $variables = array(
            'item' => "<a href='{$targetUrl}'>{$target['title']}</a>",
            'amount' => $data['amount'],
            'note' => $data['note'],
        );

        $message = StringToolkit::template($message, $variables);
        $this->getNotificationService()->notify($order['userId'], 'default', $message);
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
