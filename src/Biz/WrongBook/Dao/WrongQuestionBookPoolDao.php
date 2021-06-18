<?php

namespace Biz\WrongBook\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WrongQuestionBookPoolDao extends AdvancedDaoInterface
{
    public function getPoolByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
}
