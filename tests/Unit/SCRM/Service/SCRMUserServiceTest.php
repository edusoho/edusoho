<?php

namespace Tests\Unit\SCRM\Service;

use Biz\BaseTestCase;

class SCRMUserServiceTest extends BaseTestCase
{
    public function testGetUserByToken()
    {
        return $this->getSCRMUserService();
    }

    protected function getSCRMUserService()
    {
        return $this->biz->service('SCRM:SCRMUserService');
    }
}
