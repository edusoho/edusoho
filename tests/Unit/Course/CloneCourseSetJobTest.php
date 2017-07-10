<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Common\Logger;
use Biz\Course\Job\CloneCourseSetJob;
use Biz\Course\Job\RefreshLearningProgressJob;
use Biz\System\Service\LogService;

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
