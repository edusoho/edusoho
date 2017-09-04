<?php

namespace Codeages\Biz\Framework\Pay\Status;

class PayingStatus extends AbstractStatus
{
    const NAME = 'paying';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => PayingStatus::NAME,
        ));
    }

    public function paid($data)
    {
        // TODO
    }

    public function closing()
    {
        return $this->getPayStatus(ClosingStatus::NAME)->process();
    }
}