<?php

namespace AppBundle\Controller\AdminV2\Trade;

use AppBundle\Common\MathToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\OrderFacade\Service\OrderRefundService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;

class OrderRefundController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $this->prepareRefundSearchConditions($request->query->all());

        $paginator = new Paginator(
            $request,
            $this->getOrderRefundService()->countRefunds($conditions),
            20
        );

        $refunds = $this->getOrderRefundService()->searchRefunds(
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

        return $this->render(
            'admin-v2/trade/order-refund/list.html.twig', array(
            'refunds' => $refunds,
            'users' => $users,
            'orders' => $orders,
            'paginator' => $paginator,
        ));
    }

    public function refundDetailAction(Request $request, $id)
    {
        $orderRefund = $this->getOrderRefundService()->getOrderRefundById($id);
        $applyUser = $this->getUserService()->getUser($orderRefund['user_id']);
        $dealUser = empty($orderRefund['deal_user_id']) ? null : $this->getUserService()->getUser($orderRefund['deal_user_id']);
        $order = $this->getOrderService()->getOrder($orderRefund['order_id']);

        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        return $this->render('admin-v2/trade/order-refund/detail-modal.html.twig', array(
            'orderRefund' => $orderRefund,
            'order' => $order,
            'applyUser' => $applyUser,
            'dealUser' => $dealUser,
            'paymentTrade' => $paymentTrade,
        ));
    }

    public function auditRefundAction(Request $request, $refundId)
    {
        $refund = $this->getOrderRefundService()->getOrderRefundById($refundId);
        $order = $this->getOrderService()->getOrder($refund['order_id']);
        $trade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);
        $user = $this->getUserService()->getUser($refund['user_id']);
        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            if ('pass' === $fields['result']) {
                $refundData = array(
                    'deal_reason' => $fields['note'],
                    'refund_coin_amount' => $fields['refund_coin_amount'] * 100,
                    'refund_cash_amount' => $fields['refund_cash_amount'] * 100,
                );
                $product = $this->getOrderRefundService()->adoptRefund($refund['order_id'], $refundData);
            } else {
                $refundData = array('deal_reason' => $fields['note']);
                $product = $this->getOrderRefundService()->refuseRefund($refund['order_id'], $refundData);
            }
            $this->sendAuditRefundNotification($product, $order, $fields);
            $this->setFlashMessage('success', 'admin.order_refund_handle.success');

            return $this->redirect($this->generateUrl('admin_v2_order_refunds', array('targetType' => $product->targetType)));
        }

        return $this->render('admin-v2/trade/order-refund/refund-confirm-modal.html.twig', array(
            'order' => $order,
            'refund' => $refund,
            'user' => $user,
            'trade' => $trade,
        ));
    }

    protected function prepareRefundSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (!empty($conditions['refundItemType'])) {
            $conditions['order_item_refund_target_type'] = $conditions['refundItemType'];
        }

        if (!empty($conditions['orderRefundSn'])) {
            $conditions['sn'] = $conditions['orderRefundSn'];
            unset($conditions['orderRefundSn']);
        }

        if (!empty($conditions['orderSn'])) {
            $order = $this->getOrderService()->getOrderBySn($conditions['orderSn']);
            $conditions['order_id'] = !empty($order) ? $order['id'] : -1;
            unset($conditions['orderSn']);
        }

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['user_id'] = !empty($user) ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    protected function sendAuditRefundNotification($product, $order, $data)
    {
        $amount = MathToolkit::simple($order['pay_amount'], 0.01);
        if (isset($data['result']) && 'pass' === $data['result']) {
            $amount = $data['refund_coin_amount'] + $data['refund_cash_amount'];
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
            'item' => "<a href='{$targetUrl}'>" . $product->title . '</a>',
            'amount' => $amount,
            'note' => $data['note'],
        );

        $message = StringToolkit::template($message, $variables);
        $this->getNotificationService()->notify($order['created_user_id'], 'default', $message);
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->createService('OrderFacade:OrderRefundService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
