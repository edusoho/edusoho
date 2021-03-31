<?php

namespace Tests\Unit\Sign\Job;

use Biz\BaseTestCase;
use Biz\Sign\Dao\SignUserStatisticsDao;
use Biz\Sign\Job\RefreshUserSignKeepDaysJob;

class RefreshUserSignKeepDaysJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $sign1 = $this->createSignStatistics(['lastSignTime' => strtotime('-3 day')]);
        $sign2 = $this->createSignStatistics(['userId' => 2]);

        $job = new RefreshUserSignKeepDaysJob([], $this->getBiz());
        $job->execute();
        $sign1After = $this->getSignUserStatisticsDao()->get($sign1['id']);
        $sign2After = $this->getSignUserStatisticsDao()->get($sign2['id']);
        $this->assertEquals('3', $sign1['keepDays']);
        $this->assertEquals('0', $sign1After['keepDays']);
        $this->assertEquals($sign2, $sign2After);
    }

    private function createSignStatistics(array $sign = [])
    {
        return $this->getSignUserStatisticsDao()->create(array_merge([
            'userId' => 1,
            'targetType' => 'classroom_sign',
            'targetId' => '1',
            'signDays' => 4,
            'keepDays' => 3,
            'lastSignTime' => time(),
        ], $sign));
    }

    /**
     * @return SignUserStatisticsDao
     */
    private function getSignUserStatisticsDao()
    {
        return $this->getBiz()->dao('Sign:SignUserStatisticsDao');
    }
}
