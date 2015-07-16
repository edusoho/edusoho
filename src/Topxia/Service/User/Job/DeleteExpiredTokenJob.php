<?php
namespace Topxia\Service\User\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class DeleteExpiredTokenJob implements Job
{
    public function execute($params)
    {
        $limit = 10000;
        $number = $this->getTokenService()->deleteExpiredTokens($limit);
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
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
