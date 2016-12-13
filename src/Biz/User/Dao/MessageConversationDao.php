<?php

namespace Biz\User\Dao;

interface MessageConversationDao
{
    public function getByFromIdAndToId($fromId, $toId);

    public function findByToId($toId, $start, $limit);

    public function countByToId($toId);

}
