<?php

namespace Codeages\Biz\Pay\Status;

class ClosingStatus extends AbstractStatus
{
    const NAME = 'closing';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->getPayTradeDao()->update($this->PayTrade['id'], array(
            'status' => self::NAME,
        ));
    }

    public function closed()
    {
        return $this->getPayStatus(ClosedStatus::NAME)->process();
    }
}