<?php

namespace Biz\InformationCollect\Service;

interface EventService
{
    public function getEventByActionAndLocation($action, array $location);
}
