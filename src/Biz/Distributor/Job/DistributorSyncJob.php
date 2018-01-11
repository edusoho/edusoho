<?php

namespace Biz\Distributor\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Biz\Distributor\Util\DistributorJobStatus;

class DistributorSyncJob extends AbstractJob
{
    public function execute()
    {
        $drpService = $this->getDistributorService()->getDrpService();
        if (!empty($drpService)) {
            $jobData = $this->getDistributorService()->findJobData();
            if (!empty($jobData)) {
                $status = DistributorJobStatus::$ERROR;
                try {
                    $result = $drpService->postData($jobData, $this->getDistributorService()->getSendType());

                    if ('success' == $result['code']) {
                        $status = DistributorJobStatus::$FINISHED;
                    }
                } catch (\Exception $e) {
                    $this->biz['logger']->error(
                        'distributor send job error DistributorSyncJob::execute '.$e->getMessage(),
                        array('jobData' => $jobData, 'trace' => $e->getTraceAsString())
                    );
                }

                $this->getDistributorService()->batchUpdateStatus($jobData, $status);

                $this->getJobDao()->update(
                    $this->id, 
                    array(
                        'args' => array(
                            'type' => $this->getDistributorService()->getNextJobType()
                        )
                    )
                );
            }
        }
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function getDistributorService()
    {
        $args = $this->__get('args');

        return $this->biz->service('Distributor:Distributor'.$args['type'].'Service');
    }
}
