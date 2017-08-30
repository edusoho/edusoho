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
            try {
                $this->beginTransaction();
                $product->applyRefund();
                $this->getOrderRefundService()->applyOrderRefund($order['id'], array(
                    'reason' => $fileds['reason']['note'],
                )); 
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
