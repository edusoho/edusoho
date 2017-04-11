<?php

namespace Biz\Event\Service\Impl;

use Biz\BaseService;
use Biz\Event\Service\EventSubject;

class CourseSubject extends BaseService implements EventSubject
{
    public function getSubject($subjectId)
    {
        if (empty($subjectId)) {
            return null;
        }

        return $this->getCourseService()->getCourse($subjectId);
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
