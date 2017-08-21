<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class AdoptStatus extends AbstractRefundStatus
{
    const NAME = 'adopt';

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }

    public function finish()
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'status' => FinishStatus::NAME
        ));



        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => FinishStatus::NAME
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => FinishStatus::NAME
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;
        return $orderRefund;
    }
}