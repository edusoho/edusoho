<?php

namespace Biz\User\Dao;

interface MessageRelationDao
{
    public function updateByConversationId($conversationId, array $isRead);

    public function deleteByConversationId($conversationId);

    public function deleteByConversationIdAndMessageId($conversationId, $messageId);

    public function searchByConversationId($conversationId, $start, $limit);

    public function getByConversationIdAndMessageId($conversationId, $messageId);
}
