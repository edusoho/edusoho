<?php

namespace Tests\Unit\System;

use Biz\BaseTestCase;

class SessionServiceTest extends BaseTestCase
{
    public function testGet()
    {
        //...nothing here
    }

    protected function getSessionService()
    {
        return $this->createService('System:SessionService');
    }
}
