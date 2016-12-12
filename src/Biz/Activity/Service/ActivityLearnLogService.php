<?php

namespace Biz\Activity\Service;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);

    public function sumLearnTimeByActivityId($activityId);
}
