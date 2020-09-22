<?php

namespace Biz\InformationCollect\Service;

interface EventService
{
    public function getEventByActionAndLocation($action, array $location);

    public function get($id);

    public function findItemsByEventId($eventId);
}
