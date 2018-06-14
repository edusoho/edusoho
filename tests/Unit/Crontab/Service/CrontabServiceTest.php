<?php

namespace Tests\Unit\Crontab\Service;

use Biz\BaseTestCase;
use Biz\Crontab\Service\CrontabService;

class CrontabServiceTest extends BaseTestCase
{
    public function testSetNextExcutedTime()
    {
        $expectedTime = 0;
        $this->getCrontabService()->setNextExcutedTime($expectedTime);
        $nextExcutedTime = $this->getCrontabService()->getNextExcutedTime();

        $this->assertEquals($expectedTime, $nextExcutedTime);
    }

    /**
     * @return CrontabService
     */
    protected function getCrontabService()
    {
        return $this->createService('Crontab:CrontabService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
