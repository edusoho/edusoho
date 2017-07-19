<?php

namespace Biz\User\Job;

use Biz\User\Service\TokenService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Topxia\Service\Common\ServiceKernel;

class DeleteExpiredTokenJob extends AbstractJob
{
    public function execute()
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
