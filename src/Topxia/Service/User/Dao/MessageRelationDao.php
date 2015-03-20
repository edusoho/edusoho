<?php

namespace Topxia\Service\User\Dao;

interface MessageRelationDao
{
    public function addRelation($relation);

    public function deleteRelation($id);

    public function updateRelationIsReadByConversationId($conversationId, array $isRead);

    public function updateRelation($id, $toUpdateRelation);

    public function deleteRelationByConversationId($conversationId);

    public function getRelationCountByConversationId($conversationId);

    public function deleteConversationMessage($conversationId, $messageId);

    public function findRelationsByConversationId($conversationId, $start, $limit);

    public function getRelation($id);

    public function getRelationByConversationIdAndMessageId($conversationId, $messageId);
}