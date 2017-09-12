<?php

namespace Codeages\Biz\Framework\Pay\Status;

class ClosingStatus extends AbstractStatus
{
    const NAME = 'closing';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->getPaymentTradeDao()->update($this->paymentTrade['id'], array(
            'status' => self::NAME,
        ));
    }

    public function closed()
    {
        return $this->getPayStatus(ClosedStatus::NAME)->process();
    }
}