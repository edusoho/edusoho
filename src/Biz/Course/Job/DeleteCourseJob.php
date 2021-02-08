<?php

namespace Biz\Course\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeleteCourseJob extends AbstractJob
{
    public function execute()
    {
        $args = $this->args;
        $courseId = $args['courseId'];
    }
}
