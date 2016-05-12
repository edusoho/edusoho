<?php

namespace Topxia\Service\IM;

interface ConversationService
{
    public function getConversationByMemberIds(array $userIds);

    public function addConversation($conversation);
}
