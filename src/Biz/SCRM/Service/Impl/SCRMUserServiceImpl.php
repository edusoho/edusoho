<?php

namespace Biz\SCRM\Service\Impl;

use Biz\BaseService;
use Biz\SCRM\Service\SCRMUserService;
use ESCloud\SDK\Service\ScrmService;

class SCRMUserServiceImpl extends BaseService implements SCRMUserService
{
    public function getUserByToken($token)
    {
        $result = $this->getSCRMSdk()->getUserByToken($token);
        $user = $this->formatUser($result);
    }

    protected function formatUser($user)
    {
        return $user;
    }

    /**
     * @return ScrmService
     */
    protected function getSCRMSdk()
    {
        $biz = $this->biz;

        return $biz['ESCloudSdk.scrm'];
    }
}
