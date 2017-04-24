<?php

namespace Biz\User\Job;

use Biz\User\Service\TokenService;
use Biz\Crontab\Service\Job;
use Topxia\Service\Common\ServiceKernel;

class DeleteExpiredTokenJob implements Job
{
    public function execute($params)
    {
        $limit = 10000;
        $this->getTokenService()->deleteExpiredTokens($limit);
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
