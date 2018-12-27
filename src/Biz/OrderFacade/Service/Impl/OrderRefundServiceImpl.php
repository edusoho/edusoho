<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\BaseService;
use Biz\Order\OrderException;
use Biz\OrderFacade\Service\OrderRefundService;
use Biz\User\UserException;
use Codeages\Biz\Order\Service\OrderService;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function searchRefunds($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrderRedoundService()->searchRefunds($conditions, $orderBy, $start, $limit);
    }

    public function countRefunds($conditions)
    {
        return $this->getOrderRedoundService()->countRefunds($conditions);
    }

    public function getOrderRefundById($id)
    {
        return $this->getOrderRedoundService()->getOrderRefundById($id);
    }

    public function applyOrderRefund($orderId, $fields)
    {
        $order = $this->getOrderService()->getOrder($orderId);
        list($product, $orderItem) = $this->getProductAndOrderItem($order);

        $user = $this->getCurrentUser();

        $canApplyOrderRefund = ($user->getId() == $order['created_user_id']) && ($order['pay_amount'] > 0) && ($order['refund_deadline'] > time());
        $refunds = $this->getOrderRedoundService()->searchRefunds(array('order_id' => $order['id'], 'status' => 'auditing'), array(), 0, PHP_INT_MAX);
        if (count($refunds) >= 1) {
            $refund = $refunds[0];
            $canApplyOrderRefund = false;
        }
        if ($canApplyOrderRefund) {
            try {
                $this->beginTransaction();
                $refund = $this->getWorkflowService()->applyOrderRefund($order['id'], array(
                    'reason' => $fields['reason'],
                ));
                $this->notifyStudent($product);
                $this->notifyAdmins($product);
                $this->commit();
            } catch (\Exception $exception) {
                $this->rollback();
                throw $exception;
            }
        }

        return $refund;
    }

    public function refuseRefund($orderId, $data)
    {
        $this->tryManageOrderRefund();
        $order = $this->getOrderService()->getOrder($orderId);
        list($product, $orderItem) = $this->getProductAndOrderItem($order);

        try {
            $this->beginTransaction();
            $this->getWorkflowService()->refuseRefund($orderItem['refund_id'], $data);
            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }

        return $product;
    }

    public function adoptRefund($orderId, $data)
    {
        $this->tryManageOrderRefund();
        $order = $this->getOrderService()->getOrder($orderId);

        list($product, $orderItem) = $this->getProductAndOrderItem($order);
        try {
            $this->beginTransaction();
            $this->getWorkflowService()->adoptRefund($orderItem['refund_id'], $data);
            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }

        return $product;
    }

    public function cancelRefund($orderId)
    {
        $user = $this->getCurrentUser();
        $order = $this->getOrderService()->getOrder($orderId);
        if ($user->getId() != $order['user_id']) {
            $this->createAccessDeniedException();
        }

        list($product, $orderItem) = $this->getProductAndOrderItem($order);
        try {
            $this->beginTransaction();
            $this->getWorkflowService()->cancelRefund($orderItem['refund_id']);
            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    private function tryManageOrderRefund()
    {
        $user = $this->getCurrentUser();
        if (!$user->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }
    }

    private function getProductAndOrderItem($order)
    {
        if (empty($order)) {
            $this->createNewException(OrderException::NOTFOUND_ORDER());
        }
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        if (empty($orderItems)) {
            $this->createNewException(OrderException::NOTFOUND_ORDER_ITEMS());
        }
        $orderItem = reset($orderItems);

        $params = array('targetId' => $orderItem['target_id'], 'orderId' => $order['id'], 'orderItemId' => $orderItem['id']);
        $product = $this->getOrderFacadeService()->getOrderProduct($orderItem['target_type'], $params);

        return array($product, $orderItem);
    }

    protected function notifyStudent($product)
    {
        $user = $this->getCurrentUser();
        $setting = $this->getSettingService()->get('refund', array());

        $isNotify = empty($setting['applyNotification']) ? 0 : 1;
        if (!$isNotify) {
            return;
        }

        $message = array(
            'type' => 'apply_create',
            'targetId' => $product->targetId,
            'targetType' => $product->targetType,
            'title' => $product->title,
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        );

        $this->getNotificationService()->notify($user['id'], 'order-refund', $message);
    }

    protected function notifyAdmins($product)
    {
        $user = $this->getCurrentUser();

        $admins = $this->getUserService()->searchUsers(
            array('roles' => 'ADMIN'),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $message = array(
            'type' => 'admin_operate',
            'targetId' => $product->targetId,
            'targetType' => $product->targetType,
            'title' => $product->title,
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        );

        foreach ($admins as $key => $admin) {
            $this->getNotificationService()->notify($admin['id'], 'order-refund', $message);
        }
    }

    /**
     * @return \Codeages\Biz\Order\Service\OrderRefundService
     */
    protected function getOrderRedoundService()
    {
        return $this->createService('Order:OrderRefundService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
