<?php

namespace Biz\Event\Service\Impl;

use Biz\BaseService;
use Biz\Event\Service\EventSubject;

class ClassroomSubject extends BaseService implements EventSubject
{
    public function getSubject($subjectId)
    {
        if (empty($subjectId)) {
            return null;
        }

        return $this->getClassroomService()->getClassroom($subjectId);
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
