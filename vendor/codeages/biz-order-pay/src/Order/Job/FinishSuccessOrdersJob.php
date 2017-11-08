<?php

namespace Codeages\Biz\Order\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Order\Service\WorkflowService;

class FinishSuccessOrdersJob extends AbstractJob
{
    public function execute()
    {
        $this->getWorkflowService()->finishSuccessOrders();
    }

    /**
     * @return WorkflowService
     */
    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }
}