<?php
namespace Topxia\Service\User\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class DeleteSessionJob implements Job
{
    public function execute($params)
    {
      $number = $this->getSessionService()->deleteInvalidSession();
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
