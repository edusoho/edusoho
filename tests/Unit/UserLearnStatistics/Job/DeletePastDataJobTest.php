<?php

namespace Tests\Unit\UserLearnStatistics\Job;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Job\DeletePastDataJob;

class DeletePastDataJobTest extends BaseTestCase
{
    public function testExcute()
    {
        $service = $this->mockBiz('UserLearnStatistics:LearnStatisticsService',
            array(
                array(
                    'functionName' => 'getStatisticsSetting',
                    'returnValues' => array('timespan' => time() - 3600),
                ),
                array(
                    'functionName' => 'batchDeletePastDailyStatistics',
                    'returnValues' => array(),
                ),
            )
        );

        $job = new DeletePastDataJob(array(), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);

        $service->shouldHaveReceived('batchDeletePastDailyStatistics')->times(1);
    }
}
