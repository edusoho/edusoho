<?php

namespace Codeages\Biz\Framework\Pay\Status;

class ClosedStatus extends AbstractStatus
{
    const NAME = 'closed';

    public function getName()
    {
        self::NAME;
    }

    public function process($data = array())
    {
        $trade = $this->getPaymentTradeDao()->update($this->paymentTrade['id'], array(
            'status' => ClosedStatus::NAME,
        ));
        return $this->getAccountService()->releaseCoin($trade['user_id'], $trade['coin_amount']);
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }
}