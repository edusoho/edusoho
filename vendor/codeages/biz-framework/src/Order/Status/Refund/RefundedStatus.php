<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundedStatus extends AbstractRefundStatus
{
    const NAME = 'refunded';

    public function getName()
    {
        return self::NAME;
    }

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }

    public function process($data = array())
    {
        $orderRefund = $this->changeStatus(self::NAME);
        $this->getOrderService()->setOrderRefunded($orderRefund['order_id']);
        return $orderRefund;
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }
}