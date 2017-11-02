<?php

namespace Codeages\Biz\Pay\Status;

class ClosedStatus extends AbstractStatus
{
    const NAME = 'closed';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $trade = $this->getPayTradeDao()->update($this->PayTrade['id'], array(
            'status' => ClosedStatus::NAME,
        ));
        return $this->getAccountService()->releaseCoin($trade['user_id'], $trade['coin_amount']);
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }
}