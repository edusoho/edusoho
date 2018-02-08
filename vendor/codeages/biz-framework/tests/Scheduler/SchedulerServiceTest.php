<?php

namespace Tests;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class SchedulerServiceTest extends IntegrationTestCase
{
    public function testCreateJobProcess()
    {
        $process = array(
            'pid' => 1234,
            'start_time' => 10000,
            'end_time' => 12000,
            'cost_time' => 2000,
            'peak_memory' => 30000,
        );

        $result = $this->getSchedulerService()->createJobProcess($process);
        $this->assertEquals(10000, $result['start_time']);
        $this->assertEquals(1234, $result['pid']);
    }

    public function testUpdateJobProcess()
    {
        $process = array(
            'pid' => 1234,
            'start_time' => 10000,
            'end_time' => 12000,
            'cost_time' => 2000,
            'peak_memory' => 30000,
        );

        $result = $this->getSchedulerService()->createJobProcess($process);
        $this->assertEquals(10000, $result['start_time']);
        $this->assertEquals(1234, $result['pid']);

        $result1 = $this->getSchedulerService()->updateJobProcess($result['id'], array('pid' => 12345));
        $this->assertEquals(12345, $result1['pid']);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
