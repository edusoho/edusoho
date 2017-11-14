<?php

namespace Codeages\Biz\Order\Status\Order;

class FinishedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'finished';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }
}