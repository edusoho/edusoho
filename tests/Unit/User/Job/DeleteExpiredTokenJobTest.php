<?php

namespace Tests\Unit\User\Job;

use Biz\BaseTestCase;
use Biz\User\Job\DeleteExpiredTokenJob;

class DeleteExpiredTokenJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $tokenService = $this->mockBiz(
            'User:TokenService',
            array(
                array(
                    'functionName' => 'deleteExpiredTokens',
                    'returnValues' => array(),
                ),
            )
        );

        $job = new DeleteExpiredTokenJob(array(), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);

        $tokenService->shouldHaveReceived('deleteExpiredTokens')->times(1);
    }

    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
