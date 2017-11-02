<?php

namespace Codeages\Biz\Order\Status\Refund;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class AuditingStatus extends AbstractRefundStatus
{
    const NAME = 'auditing';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $orderRefund = $this->createOrderRefund($data['orderId'], $data);
        return $this->createOrderRefundItems($data['orderItemIds'], $orderRefund);
    }

    protected function createOrderRefund($orderId, $data)
    {
        $order = $this->getOrderDao()->get($orderId);
        if (empty($order)) {
            throw new NotFoundException("order #{$orderId} is not found.");
        }

        if ($this->biz['user']['id'] != $order['user_id']) {
            throw new AccessDeniedException("order #{$orderId} can not refund.");
        }

        $orderRefund = $this->getOrderRefundDao()->create(array(
            'order_id' => $order['id'],
            'title' => $order['title'],
            'order_item_id' => 0,
            'sn' => $this->generateSn(),
            'user_id' => $order['user_id'],
            'created_user_id' => $this->biz['user']['id'],
            'reason' => empty($data['reason']) ? '' : $data['reason'],
            'amount' => $order['pay_amount'],
            'status' => self::NAME,
            'refund_cash_amount' => $order['paid_cash_amount'],
            'refund_coin_amount' => $order['paid_coin_amount'],
        ));

        return $orderRefund;
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function createOrderRefundItems($orderItemIds, $orderRefund)
    {
        $totalAmount = 0;
        $orderItemRefunds = array();
        foreach ($orderItemIds as $orderItemId) {
            $orderItem = $this->getOrderItemDao()->get($orderItemId);
            $orderItemRefund = $this->getOrderItemRefundDao()->create(array(
                'order_refund_id' => $orderRefund['id'],
                'order_id' => $orderRefund['order_id'],
                'order_item_id' => $orderItemId,
                'user_id' => $orderRefund['user_id'],
                'created_user_id' => $this->biz['user']['id'],
                'amount' => $orderItem['pay_amount'],
                'target_type' => $orderItem['target_type'],
                'target_id' => $orderItem['target_id']
            ));

            $orderItem = $this->getOrderItemDao()->update($orderItem['id'], array(
                'refund_id' => $orderRefund['id'],
                'refund_status' => 'auditing'
            ));

            $totalAmount = $totalAmount + $orderItem['pay_amount'];

            $orderItemRefunds[] = $orderItemRefund;
        }

        $orderRefund = $this->getOrderRefundDao()->update($orderRefund['id'], array('amount' => $totalAmount));
        $orderRefund['orderItemRefunds'] = $orderItemRefunds;
        return $orderRefund;
    }

    public function refunding($data)
    {
        return $this->getOrderRefundStatus(RefundingStatus::NAME)->process($data);
    }

    public function refused($data)
    {
        return $this->getOrderRefundStatus(RefusedStatus::NAME)->process($data);
    }

    public function refunded($data)
    {
        return $this->getOrderRefundStatus(RefundedStatus::NAME)->process($data);
    }

    public function cancel()
    {
        return $this->getOrderRefundStatus(CancelStatus::NAME)->process();
    }
}