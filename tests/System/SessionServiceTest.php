<?php
namespace Tests\System;

use Topxia\Service\Common\BaseTestCase;

class SessionServiceTest extends BaseTestCase
{
    public function testGet()
    {
        //...nothing here
    }

    protected function getSessionService()
    {
        return $this->getBiz()->service('System:SessionService');
    }
}
