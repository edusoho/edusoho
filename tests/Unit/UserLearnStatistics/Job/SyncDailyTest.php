<?php

namespace Tests\Unit\UserLearnStatistics\Job;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Job\SyncDaily;

class SyncDailyTest extends BaseTestCase
{
    public function testExecute()
    {
        $cursor = 1524405204;

        $this->mockBiz('UserLearnStatistics:LearnStatisticsService', array(
            array('functionName' => 'getStatisticsSetting', 'returnValue' => null),
        ));
        $this->mockBiz('Scheduler:SchedulerService', array(
            array('functionName' => 'register', 'returnValue' => null),
        ));
        $mockedJobDao = $this->mockBiz(
            'Scheduler:JobDao',
            array(
                array(
                    'functionName' => 'update',
                ),
            )
        );

        $job = new SyncDaily(array(), $this->biz);
        $job->args = array('cursor' => $cursor);
        $job->id = 123;

        $job->execute();

        $mockedJobDao->shouldHaveReceived('update');
    }
}
