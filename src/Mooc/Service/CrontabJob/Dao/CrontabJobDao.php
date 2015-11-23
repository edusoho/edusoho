<?php

namespace Mooc\Service\CrontabJob\Dao;

interface CrontabJobDao
{
    public function getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
    public function findJobByTargetTypeAndTargetId($targetType, $targetId);
}
