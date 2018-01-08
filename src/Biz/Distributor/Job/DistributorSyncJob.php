<?php

namespace Biz\Distributor\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use AppBundle\Common\ReflectionUtils;
use Biz\Distributor\Util\DistributorJobStatus;

class DistributorSyncJob extends AbstractJob
{
    public function execute()
    {
        $drpService = $this->getDistributorService()->getDrpService();

        if (!empty($drpService)) {
            $jobData = $this->getDistributorService()->findJobData();
            if (!empty($jobData)) {
                $result = ReflectionUtils::invokeMethod(
                    $drpService,
                    $this->getDistributorService()->getPostMethod(),
                    array($jobData)
                );

                if ('success' == $result['code']) {
                    $status = DistributorJobStatus::$FINISHED;
                } else {
                    $status = DistributorJobStatus::$ERROR;
                }
                $this->getDistributorService()->batchUpdateStatus($jobData, $status);

                $this->getJobDao()->update($this->id, array('args' => $this->getDistributorService()->getNextJob()));
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
