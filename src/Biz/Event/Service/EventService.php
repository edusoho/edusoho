<?php

namespace Biz\Event\Service;

interface EventService
{
    public function dispatch($eventName, $subject, array $arguments);

    public function getEventSubject($subjectType, $subjectId);
}
