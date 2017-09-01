<?php

namespace Codeages\Biz\Framework\Pay\Status;

class PaidStatus extends AbstractStatus
{
    const NAME = 'paid';

    public function getName()
    {
        return self::NAME;
    }

    public function getPriorStatus()
    {
        return array(PayingStatus::NAME);
    }

    public function refunding()
    {
        return $this->getPayStatus(RefundingStatus::NAME)->process();
    }
}