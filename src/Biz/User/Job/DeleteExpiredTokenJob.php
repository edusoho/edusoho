<?php

namespace Biz\User\Job;

use Biz\User\Service\TokenService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

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
        return $this->biz->service('User:TokenService');
    }
}
