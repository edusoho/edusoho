<?php

namespace Topxia\Service\User\Dao;

interface MessageDao
{
    public function addMessage($message);

    public function deleteMessage($id);

    public function getMessageByFromIdAndToId($fromId, $toId);

    public function getMessage($id);

    public function findMessagesByIds(array $ids);

    public function deleteMessagesByIds(array $ids);

    public function searchMessagesCount($conditions);

    public function searchMessages($conditions, $orderBy, $start, $limit);
}