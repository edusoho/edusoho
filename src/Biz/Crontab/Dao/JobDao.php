<?php

namespace Biz\Crontab\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface JobDao extends GeneralDaoInterface
{
    public function deleteByTargetTypeAndTargetId($targetType, $targetId);

    public function findByTargetTypeAndTargetId($targetType, $targetId);

    public function findByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
}
