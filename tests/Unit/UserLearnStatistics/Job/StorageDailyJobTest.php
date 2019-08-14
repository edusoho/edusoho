<?php

namespace Tests\Unit\UserLearnStatistics\Job;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Job\StorageDailyJob;

class StorageDailyJobTest extends BaseTestCase
{
    public function testExcute()
    {
        $mockedLearnStatisticsService = $this->mockBiz(
            'UserLearnStatistics:LearnStatisticsService',
            array(
                array(
                    'functionName' => 'storageDailyStatistics',
                    'returnValue' => array(),
                ),
            )
        );

        $job = new StorageDailyJob(array(), $this->biz);
        $job->args = array();

        $job->execute();

        $mockedLearnStatisticsService->shouldHaveReceived('storageDailyStatistics');
    }
}