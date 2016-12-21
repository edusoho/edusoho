<?php
namespace Biz\User\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class DeleteExpiredTokenJob implements Job
{
    public function execute($params)
    {
        $limit  = 10000;
        $this->getTokenService()->deleteExpiredTokens($limit);
    }

    protected function getTokenService()
    {
        //How to get Biz
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
