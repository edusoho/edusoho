<?php

namespace Codeages\Plumber\Tests;

use Codeages\Plumber\HourLimiter;

class HourLimiterTest extends \PHPUnit\Framework\TestCase
{
    public function testIsLimited_1()
    {
        $limiter = new HourLimiter(0, 8);

        $baseTimestamp = strtotime(date('Y-m-d 0:0:0'));

        $this->assertFalse($limiter->isLimited($baseTimestamp + 10));
        $this->assertTrue($limiter->isLimited($baseTimestamp - 10));
        $this->assertFalse($limiter->isLimited($baseTimestamp + 3600));
        $this->assertFalse($limiter->isLimited($baseTimestamp + 7200));
        $this->assertTrue($limiter->isLimited($baseTimestamp + 3600*8));
        $this->assertFalse($limiter->isLimited($baseTimestamp + 3600*8-1));
    }

    public function testIsLimited_2()
    {
        $limiter = new HourLimiter(22, 4);

        $baseTimestamp = strtotime(date('Y-m-d 22:0:0'));

        $this->assertFalse($limiter->isLimited($baseTimestamp + 10));
        $this->assertTrue($limiter->isLimited($baseTimestamp - 10));
        $this->assertTrue($limiter->isLimited($baseTimestamp + 3600*6));
        $this->assertFalse($limiter->isLimited($baseTimestamp + 3600*6-1));
    }

    public function testIsLimited_3()
    {
        $limiter = new HourLimiter(12, 14);

        $baseTimestamp = strtotime(date('Y-m-d 12:0:0'));

        $this->assertFalse($limiter->isLimited($baseTimestamp + 10));
        $this->assertTrue($limiter->isLimited($baseTimestamp - 10));
        $this->assertTrue($limiter->isLimited($baseTimestamp + 3600*2));
        $this->assertFalse($limiter->isLimited($baseTimestamp + 3600*2-1));
    }
}