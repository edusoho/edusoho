<?php

namespace Biz\OrderRefund\Service\Impl;

use Biz\BaseService;
use Biz\OrderRefund\Service\OrderRefundService;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds)
    {
        $order = $this->getOrderService()->getOrder($orderId);
        list($product, $orderItem) = $this->getProductAndOrderItem($order);

        $user = $this->getCurrentUser();
        $member = $product->getOwner($user->getId());

        $canApplyOrderRefund = ($user->getId() == $order['created_user_id']) && ($order['pay_amount'] > 0) && ($member['refundDeadline'] > time());
        if ($canApplyOrderRefund) {
            try {
                $this->beginTransaction();
                $refund = $this->getWorkflowService()->applyOrderRefund($order['id'], array(
                    'reason' => $fileds['reason']['note'],
                ));
                $product->afterApplyRefund();
                $this->notifyStudent($product);
                $this->notifyAdmins($product);
                $this->commit();
            } catch (\Exception $exception) {
                $this->rollback();
                throw $exception;
            }
        }

        return $product;
    }

    public function refuseRefund($orderId, $data)
    {
        $this->tryManageOrderRefund();
        $order = $this->getOrderService()->getOrder($orderId);
        list($product, $orderItem) = $this->getProductAndOrderItem($order);

        try {
            $this->beginTransaction();
            $this->getWorkflowService()->refuseRefund($orderItem['refund_id'], $data);
            $product->afterRefuseRefund($order);
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

            $product->afterAdoptRefund($order);
            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }

        return $product;
    }

    public function cancelRefund($orderId)
    {
        $order = $this->getOrderService()->getOrder($orderId);

        list($product, $orderItem) = $this->getProductAndOrderItem($order);
        try {
            $this->beginTransaction();
            $this->getWorkflowService()->cancelRefund($orderItem['refund_id']);
            $product->afterCancelRefund();
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
            throw $this->createAccessDeniedException('you are not allowed to do this');
        }
    }

    private function getProductAndOrderItem($order)
    {
        if (empty($order)) {
            throw $this->createAccessDeniedException('order not be found');
        }
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        if (empty($orderItems)) {
            throw $this->createAccessDeniedException('orderItems not be found');
        }
        $orderItem = reset($orderItems);

        $params = array('targetId' => $orderItem['target_id']);
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
