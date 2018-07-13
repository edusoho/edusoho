<?php

namespace Tests\Unit\User\Job;

use Biz\BaseTestCase;
use Biz\User\Job\UpdateInviteRecordOrderInfoJob;

class UpdateInviteRecordOrderInfoJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $inviteRecordService = $this->mockBiz('User:InviteRecordService', array(
            array(
                'functionName' => 'countRecords',
                'returnValue' => 100,
            ),
            array(
                'functionName' => 'flushOrderInfo',
                'returnValue' => array(),
            ),
        ));

        $job = new UpdateInviteRecordOrderInfoJob(array(), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);

        $inviteRecordService->shouldHaveReceived('countRecords')->times(1);
        $inviteRecordService->shouldHaveReceived('flushOrderInfo')->times(1);
    }
}
