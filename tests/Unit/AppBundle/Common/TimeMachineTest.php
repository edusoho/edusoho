<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\TimeMachine;

class TimeMachineTest extends BaseTestCase
{
    public function testFormatForSimple()
    {
        $timeMachine = $this->getTimeMachine();
        $date = $timeMachine->format('Y-m-d', 1387512437);
        $this->assertEquals('2013-12-20', $date);

        $time = $timeMachine->format('H:i:s', 1387512437);
        $this->assertEquals('12:07:17', $time);
    }

    public function testInSameDay()
    {
        $timeMachine = $this->getTimeMachine();
        $date2013122012717 = 1387512440;
        $date20131220000 = 1387468800;
        $this->assertTrue($timeMachine->inSameDay($date2013122012717, $date20131220000));

        $date20131219235959 = 1387468799;
        $this->assertFalse($timeMachine->inSameDay($date20131220000, $date20131219235959));
    }

    public function testGetDayTimeRange()
    {
        $timeMachine = $this->getTimeMachine();
        $range = $timeMachine->getDayTimeRange(1387512440);

        $this->assertEquals(2, count($range));
        $this->assertEquals(1387468800, $range[0]);
        $this->assertEquals(1387555200, $range[1]);
    }

    public function testDiffDays()
    {
        $timeMachine = $this->getTimeMachine();
        $diff = $timeMachine->diffDays(1387468800, 1387555200);
        $this->assertEquals(1, $diff);

        $diff = $timeMachine->diffDays(1387468800, 1387512440);
        $this->assertEquals(0, $diff);

        $diff = $timeMachine->diffDays(1443628800, 1445582204);
        $this->assertEquals(22, $diff);

        $diff = $timeMachine->diffDays(1445582204, 1443628800);
        $this->assertEquals(22, $diff);
    }

    public function testIsTimestamp()
    {
        $this->assertFalse(TimeMachine::isTimestamp('2018-01-29 19:00:01'));
        $this->assertTrue(TimeMachine::isTimestamp('1387512440'));
        $this->assertTrue(TimeMachine::isTimestamp(1387512440));
    }

    private function getTimeMachine()
    {
        return new TimeMachine('Asia/Shanghai');
    }
}
