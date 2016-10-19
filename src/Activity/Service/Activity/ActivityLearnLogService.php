<?php

namespace Activity\Service\Activity;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);
}
