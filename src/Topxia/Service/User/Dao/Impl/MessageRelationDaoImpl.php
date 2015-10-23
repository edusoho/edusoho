<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\MessageRelationDao;

class MessageRelationDaoImpl extends BaseDao implements MessageRelationDao
{
    protected $table = 'message_relation';

    public function addRelation($relation)
    {
        $affected = $this->getConnection()->insert($this->table, $relation);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert relation error.');
        }
        return $this->getRelation($this->getConnection()->lastInsertId());
    }

    public function deleteRelation($id)
    {
       return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function updateRelation($id, $toUpdateRelation)
    {
        $this->getConnection()->update($this->table, $toUpdateRelation, array('id' => $id));
        return $this->getRelation($id);
    }

    public function updateRelationIsReadByConversationId($conversationId, array $isRead)
    {
        return $this->getConnection()->update($this->table, $isRead, array(
                'conversationId' => $conversationId
            ));
    }

    public function deleteConversationMessage($conversationId, $messageId)
    {
        return $this->getConnection()->delete($this->table, array('conversationId' => $conversationId, 'messageId'=>$messageId));
    }

    public function deleteRelationByConversationId($conversationId)
    {
        return $this->getConnection()->delete($this->table, array('conversationId' => $conversationId));
    }

    public function getRelationCountByConversationId($conversationId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  conversationId = ?";
        return $this->getConnection()->fetchColumn($sql, array($conversationId));
    }

    public function findRelationsByConversationId($conversationId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE conversationId = ? ORDER BY messageId DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($conversationId));
    }

    public function getRelation($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    
    public function getRelationByConversationIdAndMessageId($conversationId, $messageId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE conversationId = ? AND messageId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($conversationId, $messageId));
    }
    
}