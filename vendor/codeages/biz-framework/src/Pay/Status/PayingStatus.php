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

    public function closing()
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => ClosingStatus::NAME,
        ));
    }
}