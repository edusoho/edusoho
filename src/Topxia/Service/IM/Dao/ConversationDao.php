<?php

namespace Topxia\Service\IM\Dao;

interface ConversationDao
{
    public function getConversation($id);

    public function getConversationByConvNo($convNo);

    public function getConversationByMemberIds(array $MemberIds);

    public function getConversationByMemberHash($memberHash);

    public function getConversationByTargetIdAndTargetType($targetId, $targetType);

    public function addConversation($conversation);

    public function searchConversations($conditions, $orderBy, $start, $limit);

    public function searchConversationCount($conditions);

    public function deleteConversationByTargetIdAndTargetType($targetId, $targetType);
}
