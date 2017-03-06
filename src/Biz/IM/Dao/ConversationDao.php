<?php

namespace Biz\IM\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ConversationDao extends GeneralDaoInterface
{
    public function getByConvNo($convNo);

    public function getByMemberIds(array $MemberIds);

    public function getByMemberHash($memberHash);

    public function getByTargetIdAndTargetType($targetId, $targetType);

    public function deleteByTargetIdAndTargetType($targetId, $targetType);
}
