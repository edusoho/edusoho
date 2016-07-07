<?php

namespace MaterialLib\Service;

use Topxia\Service\Common\BaseService as ParentService;

class BaseService extends ParentService
{
    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
