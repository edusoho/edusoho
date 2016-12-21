<?php

namespace Biz\User\Dao;

interface MessageConversationDao
{
    public function getByFromIdAndToId($fromId, $toId);

    public function searchByToId($toId, $start, $limit);
}
