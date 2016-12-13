<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\MessageConversationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MessageConversationDaoImpl extends GeneralDaoImpl implements MessageConversationDao
{
    protected $table = 'message_conversation';

    public function getByFromIdAndToId($fromId, $toId)
    {
        return $this->getByFields(array('fromId' => $fromId, 'toId' => $toId));
    }

    public function findByToId($toId, $start, $limit)
    {
        return $this->search(array('toId' => $toId), array('latestMessageTime' => 'DESC'), $start, $limit);
    }

    public function countByToId($toId)
    {
        return $this->count(array('toId' => $toId));
    }

    public function declares()
    {
        return array(
        );
    }
}
