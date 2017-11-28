<?php

namespace Codeages\Biz\Pay\Status;

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

    public function refunded($data=array())
    {
        return $this->getPayStatus(RefundedStatus::NAME)->process($data);
    }
}