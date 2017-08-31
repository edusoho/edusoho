<?php

namespace Codeages\Biz\Framework\Pay\Status;

class PayingStatus extends AbstractStatus
{
    const NAME = 'paying';

    public function getPriorStatus()
    {
        return array();
    }

    public function paid($data)
    {

    }

    public function paying()
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => PayingStatus::NAME,
        ));
    }

    public function closing()
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => ClosingStatus::NAME,
        ));
    }
}