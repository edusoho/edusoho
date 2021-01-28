<?php

namespace Biz\IM\Service;

interface ConversationService
{
    public function createConversation($title, $targetType, $targetId, $members);

    public function getConversation($id);

    public function getConversationByConvNo($convNo);

    public function getConversationByMemberIds(array $userIds);

    public function addConversation($conversation);

    public function getConversationByTarget($targetId, $targetType);

    public function searchConversations($conditions, $orderBy, $start, $limit);

    public function searchConversationCount($conditions);

    public function deleteConversationByTargetIdAndTargetType($targetId, $targetType);

    /*
     * im_member
     */

    public function getMemberByConvNoAndUserId($convNo, $userId);

    public function findMembersByConvNo($convNo);

    public function findMembersByUserIdAndTargetType($userId, $targetType);

    public function addMember($member);

    public function deleteMember($id);

    public function deleteMemberByConvNoAndUserId($convNo, $userId);

    public function deleteMembersByTargetIdAndTargetType($targetId, $targetType);

    public function joinConversation($convNo, $userId);

    public function quitConversation($convNo, $userId);

    public function addConversationMember($convNo, $members);

    public function removeConversationMember($convNo, $userId);

    public function createCloudConversation($title, $members);

    public function isImMemberFull($convNo, $limit);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function searchMemberCount($conditions);
}
