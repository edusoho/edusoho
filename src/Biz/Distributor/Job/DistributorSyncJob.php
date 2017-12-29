<?php

namespace Biz\Distributor\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DistributorSyncJob extends AbstractJob
{
    public function execute()
    {
        $syncService = $biz['distributor.sync.'.$this->args];
        $syncService->sync();
        $this->getJobDao()->update($this->id, array('args' => $syncService->getNextJob()));

        $jobData = $this->getDistributorService()->findJobData();
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function getDistributorService()
    {
        return $this->biz->service['Distributor:Distributor'.$this->args.'Service'];
    }
}
