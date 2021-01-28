<?php

namespace Codeages\Biz\Pay\Status;

class RefundingStatus extends AbstractStatus
{
    const NAME = 'refunding';

    public function getName()
    {
        return self::NAME;
    }

    public function refunded($data = array())
    {
        return $this->getPayStatus(RefundedStatus::NAME)->process($data);
    }

    public function process($data = array())
    {
        return $this->getPayTradeDao()->update($this->PayTrade['id'], array(
            'status' => RefundingStatus::NAME,
        ));
    }
}
