<?php

namespace Biz\IM\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ConversationMemberDao extends GeneralDaoInterface
{
    public function getByConvNoAndUserId($convNo, $userId);

    public function findByConvNo($convNo);

    public function findByUserIdAndTargetType($userId, $targetType);

    public function deleteByConvNoAndUserId($convNo, $userId);

    public function deleteByTargetIdAndTargetType($targetId, $targetType);
}
