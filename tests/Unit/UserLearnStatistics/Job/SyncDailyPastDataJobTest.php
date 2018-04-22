<?php

namespace Tests\Unit\UserLearnStatistics\Job;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Job\SyncDailyPastDataJob;

class SyncDailyPastDataJobTest extends BaseTestCase
{
    public function testExcute()
    {
        $currentTime = 1524405254;
        $cursor = 1524405204;
        $nextCursor = 1524318804;

        $mockedLearnStatisticsService = $this->mockBiz(
            'UserLearnStatistics:LearnStatisticsService',
            array(
                array(
                    'functionName' => 'getStatisticsSetting',
                    'withParams' => array(),
                    'returnValue' => array('currentTime' => $currentTime, 'timespan' => 100),
                ),
                array(
                    'functionName' => 'batchCreatePastDailyStatistics',
                    'withParams' => array(
                        array(
                            'createdTime_GE' => $nextCursor,
                            'createdTime_LT' => $cursor,
                            'skipSyncCourseSetNum' => true,
                            'event_EQ' => 'doing',
                        ),
                    ),
                ),
            )
        );

        $mockedJobDao = $this->mockBiz(
            'Scheduler:JobDao',
            array(
                array(
                    'functionName' => 'update',
                    'withParams' => array(
                        123,
                        array('args' => array('cursor' => $nextCursor)),
                    ),
                ),
            )
        );

        $job = new SyncDailyPastDataJob(array(), $this->biz);
        $job->args = array('cursor' => $cursor);
        $job->id = 123;

        $job->execute();

        $mockedLearnStatisticsService->shouldHaveReceived('getStatisticsSetting');
        $mockedLearnStatisticsService->shouldHaveReceived('batchCreatePastDailyStatistics');
        $mockedJobDao->shouldHaveReceived('update');

        $this->assertTrue(true);
    }
}
