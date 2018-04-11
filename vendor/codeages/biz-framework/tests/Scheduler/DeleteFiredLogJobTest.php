<?php

namespace Tests;

use Codeages\Biz\Framework\Scheduler\Job\DeleteFiredLogJob;

class DeleteFiredLogJobTest extends IntegrationTestCase
{
    public function testExecute()
    {
        $schedulerService = $this->mockObjectIntoBiz(
            'Scheduler:SchedulerService',
            array(
                array(
                    'functionName' => 'deleteUnacquiredJobFired',
                    'withParams' => array(15),
                ),
            )
        );

        $job = new DeleteFiredLogJob(array(), $this->biz);
        $job->execute();

        $schedulerService->shouldHaveReceived('deleteUnacquiredJobFired')->times(1);
        $this->assertTrue(true);
    }
}
