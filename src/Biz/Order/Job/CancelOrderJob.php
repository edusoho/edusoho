<?php

namespace Biz\Order\Job;

use Codeages\Biz\Framework\Order\Service\WorkflowService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CancelOrderJob extends AbstractJob
{
    public function execute()
    {
        $this->getWorkflowService()->closeOrders();
    }

    /**
     * @return WorkflowService
     */
    private function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }
}
