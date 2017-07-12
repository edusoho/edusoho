<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Job\CloneCourseSetJob;

class CloneCourseSetJobTest extends BaseTestCase
{
    public function testExecuteWithOutCourseJob()
    {
        $this->mockService();
        $job = new CloneCourseSetJob(array('args' => array('courseSetId' => 1)), $this->getBiz());
        $job->execute();
    }

    private function mockService()
    {
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'cloneCourseSet', 'returnValue' => ''),
        ));
    }
}
