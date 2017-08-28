<?php

namespace Codeages\Biz\Framework\Pay\Status;

class ClosingStatus extends AbstractStatus
{
    const NAME = 'closing';

    public function getPriorStatus()
    {
        return array(PayingStatus::NAME);
    }

    public function closed()
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => ClosedStatus::NAME,
        ));
    }
}