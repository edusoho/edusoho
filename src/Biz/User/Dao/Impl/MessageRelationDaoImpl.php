<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\MessageRelationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MessageRelationDaoImpl extends GeneralDaoImpl implements MessageRelationDao
{
    protected $table = 'message_relation';

    public function updateByConversationId($conversationId, array $fields)
    {
        return $this->db()->update($this->table, $fields, array(
            'conversationId' => $conversationId,
        ));
    }

    public function deleteByConversationIdAndMessageId($conversationId, $messageId)
    {
        return $this->db()->delete($this->table, array('conversationId' => $conversationId, 'messageId' => $messageId));
    }

    public function deleteByConversationId($conversationId)
    {
        return $this->db()->delete($this->table, array('conversationId' => $conversationId));
    }

    public function searchByConversationId($conversationId, $start, $limit)
    {
        return $this->search(array('conversationId' => $conversationId), array('messageId' => 'DESC'), $start, $limit);
    }

    public function getByConversationIdAndMessageId($conversationId, $messageId)
    {
        return $this->getByFields(array('conversationId' => $conversationId, 'messageId' => $messageId));
    }

    public function declares()
    {
        return array(
            'orderbys' => array('messageId'),
            'conditions' => array('conversationId = :conversationId'),
        );
    }
}
