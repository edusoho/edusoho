<?php

namespace Codeages\Biz\Framework\Order\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloseOrdersJob extends AbstractJob
{
    public function execute()
    {
        $this->getOrderService()->closeOrders();
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }
}