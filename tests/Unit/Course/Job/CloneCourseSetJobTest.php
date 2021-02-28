<?php

namespace Tests\Unit\Course\Job;

use Biz\BaseTestCase;
use Biz\Course\Job\CloneCourseSetJob;

class CloneCourseSetJobTest extends BaseTestCase
{
    public function testExecuteWithOutCourseJob()
    {
        $this->mockService();
        $job = new CloneCourseSetJob(['args' => ['courseSetId' => 1, 'params' => ['title' => 'testTitle2'], 'userId' => $this->getCurrentUser()->id]], $this->getBiz());
        $job->execute();
    }

    private function mockService()
    {
        $this->mockBiz('Course:CourseSetService', [
            ['functionName' => 'cloneCourseSet', 'returnValue' => ''],
            ['functionName' => 'getCourseSet', 'returnValue' => ['id' => 1, 'title' => 'testTitle1']],
        ]);
    }
}
