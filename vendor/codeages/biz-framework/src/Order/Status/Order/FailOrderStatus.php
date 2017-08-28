<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class FailOrderStatus extends AbstractOrderStatus
{
    const NAME = 'fail';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }

    public function getPriorStatus()
    {
        return array(PaidOrderStatus::NAME);
    }

    public function success($data = array())
    {
        return $this->getOrderStatus(SuccessOrderStatus::NAME)->process($data);
    }
}