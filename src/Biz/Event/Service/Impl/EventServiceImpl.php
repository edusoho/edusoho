<?php

namespace Biz\Event\Service\Impl;

use Biz\BaseService;
use Biz\Event\Service\EventService;

class EventServiceImpl extends BaseService implements EventService
{
    public function dispatched($eventName, $subject, $arguments = array())
    {
        $this->dispatchEvent($eventName, $subject, $arguments);
    }

    public function getEventSubject($subjectType, $subjectId)
    {
        return EventSubjectFactory::create($subjectType)->getSubject($subjectId);
    }
}
