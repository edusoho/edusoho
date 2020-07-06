<?php

namespace Biz\Review\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ReviewDao extends GeneralDaoInterface
{
    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);

    public function sumRatingByConditions($conditions);

    public function deleteByParentId($parentId);

    public function deleteByTargetTypeAndTargetId($targetType, $targetId);
}
