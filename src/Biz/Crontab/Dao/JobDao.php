<?php

namespace Biz\Crontab\Dao;

interface JobDao
{
    public function deleteByTargetTypeAndTargetId($targetType, $targetId);

    public function findByTargetTypeAndTargetId($targetType, $targetId);

    public function findByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
}
