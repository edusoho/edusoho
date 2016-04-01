<?php

namespace Topxia\Service\IM;

interface ConversationService
{
    public function getConversationByUserIds(array $userIds);

    public function addConversation($conversation);

}