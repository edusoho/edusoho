<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Job\RefreshLearningProgressJob;
use Biz\System\Service\LogService;

class RefreshLearningProgressJobTest extends BaseTestCase
{
    public function testExecuteWithOutCourseJob()
    {
        $job = new RefreshLearningProgressJob(array(), $this->getBiz());
        $job->execute();
    }

    public function testExecuteWithSomeCourseJob()
    {
        $job = new RefreshLearningProgressJob(array(), $this->getBiz());

        $this->mockDao();
        $this->mockService();

        $job->execute();

        $count = $this->getLogService()->searchLogCount(array('action' => 'refresh_learning_progress', 'level' => 'info'));

        $this->assertEquals(5, $count);
    }

    private function mockDao()
    {
        $fakeCourses = array(
            array('id' => 1),
            array('id' => 2),
            array('id' => 3),
        );
        $fakeCourseJobs = array(
            array('id' => 1, 'data' => array(-1)),
        );

        $this->mockBiz('Course:CourseJobDao', array(
           array('functionName' => 'findByType', 'returnValue' => $fakeCourseJobs),
           array('functionName' => 'deleteByTypeAndCourseId', 'returnValue' => 1),
        ));

        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIds', 'returnValue' => $fakeCourses),
        ));
    }

    private function mockService()
    {
        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'findMemberUserIdsByCourseId', 'returnValue' => array(1, 2, 3)),
        ));
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
