<?php
namespace Tests\System;

use Biz\BaseTestCase;;

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
