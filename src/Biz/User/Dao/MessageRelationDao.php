<?php

namespace Biz\User\Dao;

interface MessageRelationDao
{
    public function updateRelationIsReadByConversationId($conversationId, array $isRead);

    public function deleteByConversationId($conversationId);

    public function countByConversationId($conversationId);

    public function deleteByConversationIdAndMessageId($conversationId, $messageId);

    public function findByConversationId($conversationId, $start, $limit);

    public function getByConversationIdAndMessageId($conversationId, $messageId);
}
