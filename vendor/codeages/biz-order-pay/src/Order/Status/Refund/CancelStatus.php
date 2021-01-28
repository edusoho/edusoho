<?php

namespace Codeages\Biz\Order\Status\Refund;

class CancelStatus extends AbstractRefundStatus
{
    const NAME = 'cancel';

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