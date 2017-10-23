<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundedStatus extends AbstractRefundStatus
{
    const NAME = 'refunded';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $orderRefund = $this->changeStatus(self::NAME);
        return $orderRefund;
    }
}