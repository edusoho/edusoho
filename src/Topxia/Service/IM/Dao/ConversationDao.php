<?php

namespace Topxia\Service\IM\Dao;

interface ConversationDao
{
    public function getConversationByMemberIds(array $MemberIds);

    public function getConversationByMemberHash($memberHash);

    public function addConversation($conversation);
}
