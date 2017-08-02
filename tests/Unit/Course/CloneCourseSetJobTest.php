<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Job\CloneCourseSetJob;

class CloneCourseSetJobTest extends BaseTestCase
{
    public function testExecuteWithOutCourseJob()
    {
        $this->mockService();
        $job = new CloneCourseSetJob(array('args' => array('courseSetId' => 1, 'params' => array('title' => 'testTitle2'), 'userId' => $this->getCurrentUser()->id)), $this->getBiz());
        $job->execute();
    }

    private function mockService()
    {
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'cloneCourseSet', 'returnValue' => ''),
            array('functionName' => 'getCourseSet', 'returnValue' => array('id' => 1, 'title' => 'testTitle1')),
        ));
    }
}
