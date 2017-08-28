<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundingStatus extends AbstractRefundStatus
{
    const NAME = 'refunding';

    public function getName()
    {
        return self::NAME;
    }

    public function getPriorStatus()
    {
        return array(AuditingStatus::NAME);
    }

    public function refunded($data = array())
    {
        return $this->getOrderRefundStatus(RefundedStatus::NAME)->process($data);
    }

    public function process($data = array())
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => self::NAME
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => self::NAME
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => self::NAME
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;

        $this->getOrderService()->setOrderRefunding($orderRefund['order_id']);

        return $orderRefund;
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }
}