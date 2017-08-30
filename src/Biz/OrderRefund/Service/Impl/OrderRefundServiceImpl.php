<?php

namespace Biz\OrderRefund\Service\Impl;

use Biz\BaseService;
use Biz\OrderRefund\Service\OrderRefundService;
use Codeages\Biz\Framework\Event\Event;
use Biz\OrderFacade\Product\Product;
use AppBundle\Common\StringToolkit;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds)
    {
        $order = $this->getOrderService()->getOrder($orderId);
        list($product, $orderItem) = $this->getProductAndOrderItem($order);

        $canApplyOrderRefund = ($order['pay_amount'] > 0) && ($order['refund_deadline'] > time());
        if ($canApplyOrderRefund) {
            try {
                $this->beginTransaction();
                $product->applyRefund();
                $this->getOrderRefundService()->applyOrderRefund($order['id'], array(
                    'reason' => $fileds['reason']['note'],
                ));
                $this->notify($product);
                $this->commit();

                $this->dispatch('order.service.refund_pending', new Event($order, array('refund' => $refund))); 
            } catch (\Exception $exception) {
                $this->rollback();
                throw $exception;
            }
        }

        return $product;
    }

    public function cancelRefund($orderId)
    {
        $order = $this->getOrderService()->getOrder($orderId);

        list($product, $orderItem) = $this->getProductAndOrderItem($order);
        try {
            $this->beginTransaction();
            $product->cancelRefund();
            $this->getOrderRefundService()->cancelRefund($orderItem['refund_id']);

            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
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

        $product = $this->biz['order.product.'.$orderItem['target_type']];
        $product->init(array('targetId' => $orderItem['target_id']));

        return array($product, $orderItem);
    }

    private function notify($product)
    {
        //原来逻辑，需要重构
        $refundSetting = $this->getSettingService()->get('refund');
        $user = $this->getCurrentUser();
        if (!empty($refundSetting['applyNotification'])) {
            $message = $refundSetting['applyNotification'];
            $variables = array(
                'item' => $product->title
            );

            $message = StringToolkit::template($message, $variables);
            $this->getNotificationService()->notify($user->getId(), 'default', $message);
        }

        $adminmessage = sprintf('用户%s申请退款 %s 教学计划，请审核。', $user['nickname'], $product->title);
        $adminCount = $this->getUserService()->countUsers(array('roles' => 'ADMIN'));

        $admins = $this->getUserService()->searchUsers(array('roles' => 'ADMIN'), array('id' => 'DESC'), 0, $adminCount);
        foreach ($admins as $key => $admin) {
            $this->getNotificationService()->notify($admin['id'], 'default', $adminmessage);
        } 
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
        return $this->createService('Order:OrderRefundService');
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
