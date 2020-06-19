<?php

namespace Biz\Review\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ReviewDao extends GeneralDaoInterface
{
    public function getByUserIdAndTargetIdAndTargetType($userId, $targetType, $targetId);
}
