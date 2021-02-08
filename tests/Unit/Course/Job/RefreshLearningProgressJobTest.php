<?php

namespace Tests\Unit\Course\Job;

use Biz\BaseTestCase;
use Biz\Course\Job\RefreshLearningProgressJob;
use Biz\System\Service\LogService;

class RefreshLearningProgressJobTest extends BaseTestCase
{
    public function testExecuteWithOutCourseJob()
    {
        $job = new RefreshLearningProgressJob([], $this->getBiz());
        $job->execute();
    }

    public function testExecuteWithSomeCourseJob()
    {
        $job = new RefreshLearningProgressJob([], $this->getBiz());

        $this->mockDao();
        $this->mockService();

        $job->execute();

        $count = $this->getLogService()->searchLogCount(['action' => 'refresh_learning_progress', 'level' => 'info']);

        $this->assertEquals(5, $count);
    }

    private function mockDao()
    {
        $fakeCourses = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];
        $fakeCourseJobs = [
            ['id' => 1, 'data' => [-1]],
        ];

        $this->mockBiz('Course:CourseJobDao', [
           ['functionName' => 'findByType', 'returnValue' => $fakeCourseJobs],
           ['functionName' => 'deleteByTypeAndCourseId', 'returnValue' => 1],
        ]);

        $this->mockBiz('Course:CourseDao', [
            ['functionName' => 'findCoursesByParentIds', 'returnValue' => $fakeCourses],
        ]);
    }

    private function mockService()
    {
        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'findMemberUserIdsByCourseId', 'returnValue' => [1, 2, 3]],
        ]);
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
