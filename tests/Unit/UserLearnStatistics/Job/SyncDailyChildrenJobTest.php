<?php

namespace Tests\Unit\UserLearnStatistics\Job;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Job\SyncDailyChildrenJob;

class SyncDailyChildrenJobTest extends BaseTestCase
{
    public function testExcute()
    {
        $cursor = 1524405204;
        $nextCursor = 1524318804;

        $mockedLearnStatisticsService = $this->mockBiz(
            'UserLearnStatistics:LearnStatisticsService',
            array(
                array(
                    'functionName' => 'batchCreateDailyStatistics',
                    'withParams' => array(
                        array(
                            'createdTime_LT' => $nextCursor,
                            'createdTime_GE' => $cursor,
                            'skipSyncCourseSetNum' => true,
                            'event_EQ' => 'doing',
                        ),
                    ),
                ),
            )
        );

        $job = new SyncDailyChildrenJob(array(), $this->biz);
        $job->args = array('cursor' => $cursor, 'nextCursor' => $nextCursor, 'learnStatisticsTime' => $cursor);

        $job->execute();

        $mockedLearnStatisticsService->shouldHaveReceived('batchCreateDailyStatistics');
    }
}