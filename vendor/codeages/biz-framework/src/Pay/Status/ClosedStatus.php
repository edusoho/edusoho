<?php

namespace Codeages\Biz\Framework\Pay\Status;

class ClosedStatus extends AbstractStatus
{
    const NAME = 'closed';

    public function getName()
    {
        self::NAME;
    }

    public function getPriorStatus()
    {
        return array(ClosingStatus::NAME);
    }

    public function process($data = array())
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => ClosedStatus::NAME,
        ));
    }
}