<?php

namespace Topxia\Service\IM\Dao;

interface ConversationDao
{
    public function getConversationByUserIds(array $userIds);

    public function addConversation($conversation);
 
}