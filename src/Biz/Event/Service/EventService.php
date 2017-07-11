<?php

namespace Biz\Event\Service;

interface EventService
{
    public function dispatched($eventName, $subject, array $arguments);

    public function getEventSubject($subjectType, $subjectId);
}
