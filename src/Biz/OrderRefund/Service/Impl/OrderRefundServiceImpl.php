<?php

namespace Biz\OrderRefund\Service\Impl;

use Biz\BaseService;
use Biz\OrderRefund\Service\OrderRefundService;
use Codeages\Biz\Framework\Event\Event;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds)
    {
        $order = $this->getOrderService()->getOrder($orderId);
        if (empty($order)) {
            $this->createAccessDeniedException('order not be found');
        }
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($orderId);
        if (empty($orderItems)) {
            $this->createAccessDeniedException('orderItems not be found');
        }
        $orderItem = reset($orderItems); 
        $product = $this->getProduct($orderItem['target_id'], $orderItem['target_type']);

        $canApplyOrderRefund = ($order['pay_amount'] > 0) && ($order['refund_deadline'] > time());
        if (!empty($fileds['applyRefund']) && $canApplyOrderRefund) {
            $this->lockMember($product, $fileds);            
        } else {
            $product->removeMember();
        }

        return $product;
    }

    private function getProduct($id, $type)
    {
        $product = $this->biz['order.product.'.$type];
        $product->init(array('targetId' => $id));

        return $product;
    }

    private function lockMember($product, $data)
    {
        //todo事务
        //检查哪里有监听这些事件

        $product->lockMember();
        $this->dispatch('order.service.refund_pending', new Event($order, array('refund' => $refund)));
        $this->getOrderRefundService()->applyOrderRefund($order['id'], array(
            'reason' => $data['reason']['note'],
        ));
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
        return $this->getBiz()->service('Order:OrderRefundService');
    }
}
