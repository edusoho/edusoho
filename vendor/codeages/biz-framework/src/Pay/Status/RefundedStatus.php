<?php

namespace Codeages\Biz\Framework\Pay\Status;

class RefundedStatus extends AbstractStatus
{
    const NAME = 'refunded';

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => RefundedStatus::NAME,
            'refund_success_time' => time()
        ));
    }
}