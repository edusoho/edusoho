<?php

namespace Biz\WrongBook\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WrongQuestionBookPoolDao extends AdvancedDaoInterface
{
    public function getPoolByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);

    public function getPoolByFieldsGroupByTargetType($fields);

    public function getPoolBySceneId($sceneId);

    public function searchPoolByConditions($conditions, $orderBys, $start, $limit);

    public function countPoolByConditions($conditions);

    public function deleteWrongPoolByTargetIdAndTargetType($targetId,$targetType);

    public function findPoolsByTargetIdAndTargetType($targetId,$targetType);
}
