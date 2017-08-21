<?php

namespace Codeages\Biz\Framework\Order\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class FinishOrdersJob extends AbstractJob
{
    public function execute()
    {
        $this->getOrderService()->finishOrders();
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }
}