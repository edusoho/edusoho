<?php

namespace Topxia\Service\IM;

interface ConversationService
{
    public function getConversationByMemberIds(array $userIds);

    public function addConversation($conversation);

    public function addMyConversation($myConversation);

    public function updateMyConversationByNo($no, $fields);

    public function listMyConversationsByUserId($userId, $start, $limit);
}
