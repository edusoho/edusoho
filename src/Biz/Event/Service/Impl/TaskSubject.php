<?php

namespace Biz\Event\Service\Impl;

use Biz\BaseService;
use Biz\Event\Service\EventSubject;

class TaskSubject extends BaseService implements EventSubject
{
    public function getSubject($subjectId)
    {
        if (empty($subjectId)) {
            return null;
        }

        return $this->getTaskService()->getTask($subjectId);
    }

    private function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
