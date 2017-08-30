<?php

namespace Biz\OrderRefund\Service\Impl;

use Biz\BaseService;
use Biz\OrderRefund\Service\OrderRefundService;
use Codeages\Biz\Framework\Event\Event;
use Biz\OrderFacade\Product\Product;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds)
    {
        $order = $this->getOrderService()->getOrder($orderId);
        $product = $this->getProduct($order);

        $canApplyOrderRefund = ($order['pay_amount'] > 0) && ($order['refund_deadline'] > time());
        if ($canApplyOrderRefund) {
            //事务
            //检查哪里有监听这些事件
            //发送通知给管理员 （原来的逻辑）
            $product->applyRefund();
            $this->dispatch('order.service.refund_pending', new Event($order));
            $this->getOrderRefundService()->applyOrderRefund($order['id'], array(
                'reason' => $fileds['reason']['note'],
            ));        
        }

        return $product;
    }

    public function cancelRefund($orderId)
    {
        $product = $this->getProduct($orderId);
        if (!($product instanceof Product)) {
            return $product;
        }

        $product->cancelRefund();
        // $this->getOrderRefundService()->applyOrderRefund($order['id'], array(
        //     'reason' => $data['reason']['note'],
        // ));
    }

    private function getProduct($order)
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

        return $product;
    }

    private function notifyAdminUser()
    {
        
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
}
