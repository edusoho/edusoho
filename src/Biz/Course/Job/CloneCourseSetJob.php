<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloneCourseSetJob extends AbstractJob
{
    public function execute()
    {
        $this->getCourseSetService()->cloneCourseSet($this->args['courseSetId']);
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
