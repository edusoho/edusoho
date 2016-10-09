<?php

namespace Topxia\Service\IM;

interface ConversationService
{
    public function getConversation($id);

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

    public function addMember($member);

    public function deleteMember($id);

    public function deleteMemberByConvNoAndUserId($convNo, $userId);

    public function deleteMembersByTargetIdAndTargetType($targetId, $targetType);

    public function addConversationMember($convNo, $members);

    public function createCloudConversation($title, $members);

    public function isImMemberFull($convNo, $limit);

    public function searchImMembers($conditions, $orderBy, $start, $limit);

    public function searchImMemberCount($conditions);

}
