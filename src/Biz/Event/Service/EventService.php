<?php

namespace Biz\Event\Service;

interface EventService
{
    public function dispatch($eventName, $subject, $arguments = array());

    public function getEventSubject($subjectType, $subjectId);
}
