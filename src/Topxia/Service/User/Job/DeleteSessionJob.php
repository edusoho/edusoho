<?php
namespace Topxia\Service\User\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class DeleteSessionJob implements Job
{
    public function execute($params)
    {
      $retentionTime = time()-7200;
      $limit = 500;
      //$number = $this->getSessionService()->deleteInvalidSession($retentionTime, $limit);
    }

    protected function getSessionService()
    {
        return $this->getServiceKernel()->createService('System.SessionService');
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
