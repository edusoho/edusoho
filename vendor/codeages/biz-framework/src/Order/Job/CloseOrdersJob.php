<?php

namespace Codeages\Biz\Framework\Order\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloseOrdersJob extends AbstractJob
{
    public function execute()
    {
        $this->getWorkflowService()->closeOrders();
    }

    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }
}