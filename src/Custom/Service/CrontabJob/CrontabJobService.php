<?php

namespace Custom\Service\CrontabJob;

interface CrontabJobService
{
    public function getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
    public function updateJob($id, $fields);
    public function findJobByTargetTypeAndTargetId($targetType, $targetId);
}
