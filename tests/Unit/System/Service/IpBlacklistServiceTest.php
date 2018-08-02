<?php

namespace Tests\Unit\System\Service;

use Biz\BaseTestCase;

class IpBlacklistServiceTest extends BaseTestCase
{
    public function testIncreaseIpFailedCount()
    {
        $counter = $this->getIpBlacklistService()->increaseIpFailedCount('192.111.22.33');
        $this->assertEquals($counter, 1);
    }

    public function testGetIpFailedCount()
    {
        $ip = '192.111.22.33';
        $counter = $this->getIpBlacklistService()->getIpFailedCount($ip);
        $this->assertEquals($counter, 0);
        $counter = $this->getIpBlacklistService()->increaseIpFailedCount($ip);
        $this->assertEquals($counter, 1);
    }

    public function testClearFailedIp()
    {
        $ip = '192.111.22.33';
        $counter = $this->getIpBlacklistService()->increaseIpFailedCount($ip);
        $this->assertEquals($counter, 1);
        $this->getIpBlacklistService()->clearFailedIp($ip);
        $counter = $this->getIpBlacklistService()->getIpFailedCount($ip);
        $this->assertEquals($counter, 0);
    }

    protected function getIpBlacklistService()
    {
        return $this->createService('System:IpBlacklistService');
    }
}
