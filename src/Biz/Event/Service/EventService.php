<?php

namespace Biz\Event\Service;

interface EventService
{
    public function dispatched($eventName, $subject, $arguments = array());

    public function getEventSubject($subjectType, $subjectId);
}
