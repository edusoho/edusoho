<?php

namespace Tests;

use Codeages\Biz\Framework\Scheduler\Job\DeleteFiredLogJob;

class DeleteFiredLogJobTest extends IntegrationTestCase
{
    public function testExecute()
    {
        $schedulerService = $this->mockBiz(
            'Scheduler:SchedulerService',
            array(
                array(
                    'functionName' => 'deleteJobFired',
                    'withParams' => array(15),
                ),
            )
        );

        $job = new DeleteFiredLogJob(array(), $this->biz);
        $job->execute();

        $schedulerService->shouldHaveReceived('deleteJobFired')->times(1);
        $this->assertTrue(true);
    }
}
