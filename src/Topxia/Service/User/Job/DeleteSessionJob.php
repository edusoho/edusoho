<?php
namespace Topxia\Service\User\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class DeleteSessionJob implements Job
{
    public function execute($params)
    {
      $retentionTime = time()-7200;
      $number = $this->getSessionService()->deleteInvalidSession($retentionTime);
    }

    private function getSessionService()
    {
        return $this->getServiceKernel()->createService('System.SessionService');
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    private function getLogService()
    {
        return $this->getServiceKernel()->createService('Log.LogService'); 
    }
}
