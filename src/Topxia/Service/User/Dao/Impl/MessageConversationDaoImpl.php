<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\MessageConversationDao;

class MessageConversationDaoImpl extends BaseDao implements MessageConversationDao
{
    protected $table = 'message_conversation';
    
    /**
     * 表中的toId 表示的是发送者, fromId表示的是接受者,理解的立场是从系统发送角度出发，先给toId这创建conversation.
     */
    public function getConversation($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    
    public function addConversation($conversation)
    {
        $affected = $this->getConnection()->insert($this->table, $conversation);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert conversation error.');
        }
        return $this->getConversation($this->getConnection()->lastInsertId());
    }

    public function deleteConversation($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getConversationByFromIdAndToId($fromId, $toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? AND toId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($fromId, $toId));
    }

    public function updateConversation($id, $toUpdateConversation)
    {
        $this->getConnection()->update($this->table, $toUpdateConversation, array('id' => $id));
        return $this->getConversation($id);
    }

    public function findConversationsByToId($toId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? ORDER BY latestMessageTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($toId));
    }

    public function getConversationCountByToId($toId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  toId = ?";
        return $this->getConnection()->fetchColumn($sql, array($toId));
    }

}