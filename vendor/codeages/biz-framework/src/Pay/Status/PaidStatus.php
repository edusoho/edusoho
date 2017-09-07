<?php

namespace Codeages\Biz\Framework\Pay\Status;

class PaidStatus extends AbstractStatus
{
    const NAME = 'paid';

    public function getName()
    {
        return self::NAME;
    }

    public function refunding()
    {
        return $this->getPayStatus(RefundingStatus::NAME)->process();
    }

    public function refunded()
    {
        return $this->getPayStatus(RefundedStatus::NAME)->process();
    }
}