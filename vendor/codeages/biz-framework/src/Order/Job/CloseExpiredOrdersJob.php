<?php

namespace Codeages\Biz\Framework\Order\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloseExpiredOrdersJob extends AbstractJob
{
    public function execute()
    {
        $this->getWorkflowService()->closeExpiredOrders();
    }

    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }
}