<?php

namespace Tests\Unit\Course\Job;

use Biz\BaseTestCase;
use Biz\Course\Job\DeleteCourseJob;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class DeleteCourseJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->buildCourseData();
        $job = new DeleteCourseJob([
            'args' => ['courseId' => 100],
        ], $this->getBiz());

        $job->execute();
    }

    public function buildCourseData()
    {
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
