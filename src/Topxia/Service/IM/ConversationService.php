<?php

namespace Topxia\Service\IM;

interface ConversationService
{
    public function getConversationByMemberIds(array $userIds);

    public function addConversation($conversation);

    public function getMemberByConvNoAndUserId($convNo, $userId);

    public function findMembersByConvNo($convNo);

    public function addMember($member);

    public function deleteMember($id);

    public function deleteMemberByConvNoAndUserId($convNo, $userId);

    public function addConversationMember($convNo, $userId, $nickname);

    public function createCloudConversation($title, $userId, $nickname);

}
