<?php

namespace Codeages\Biz\Pay\Status;

class PayingStatus extends AbstractStatus
{
    const NAME = 'paying';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->getPayTradeDao()->update($this->PayTrade['id'], array(
            'status' => PayingStatus::NAME,
        ));
    }

    public function paid($data)
    {
        return $this->getPayStatus(PaidStatus::NAME)->process();
    }

    public function closing()
    {
        return $this->getPayStatus(ClosingStatus::NAME)->process();
    }
}