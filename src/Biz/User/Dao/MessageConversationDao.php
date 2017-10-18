<?php

namespace Biz\User\Dao;

interface MessageConversationDao
{
    public function getByFromIdAndToId($fromId, $toId);

    public function updateByConversationId($conversationId, array $fields);
}
