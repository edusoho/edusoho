<?php

namespace MaterialLib\Service;

use Topxia\Service\Common\BaseService as ParentService;
use Topxia\Service\Common\ServiceKernel;

class BaseService extends ParentService
{
    protected function getUserService()
    {
        return ServiceKernel::instance()->getBiz()->service('User:UserService');
    }
}
