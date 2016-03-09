<?php
namespace Topxia\Service\CloudData\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class CloudDataJob implements Job
{
    public function execute($params)
    {
        //$number = $this->getSessionService()->deleteInvalidSession($retentionTime, $limit);
    }

    protected function getCloudDataService()
    {
        return $this->getServiceKernel()->createService('CloudData.CloudDataService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('Log.LogService');
    }
}
