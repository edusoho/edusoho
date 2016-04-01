<?php
namespace Topxia\Service\IM\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\IM\Dao\ConversationDao;

class ConversationDaoImpl extends BaseDao implements ConversationDao
{
    protected $table = 'im_conversation';

    public $serializeFields = array(
        'userIds' => 'saw'
    );

    public function getConversationByUserIds(array $userIds)
    {
        $conversation = array(
            'userIds' => $userIds
        );
        $conversation = $this->createSerializer()->serialize($conversation, $this->serializeFields);
        $sql = "SELECT * FROM {$this->getTable()} where userIds=? LIMIT 1";
        $conversation = $this->getConnection()->fetchAssoc($sql, array($conversation['userIds']));
        return $conversation ? $this->createSerializer()->unserialize($conversation, $this->serializeFields) : null;
    }

    public function getConversation($id)
    {
        $sql = "SELECT * FROM {$this->getTable()} where id=? LIMIT 1";
        $conversation = $this->getConnection()->fetchAssoc($sql, array($id));
        return $conversation ? $this->createSerializer()->unserialize($conversation, $this->serializeFields) : null;
    }

    public function addConversation($conversation)
    {
        $conversation = $this->createSerializer()->serialize($conversation, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $conversation);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Conversation error.');
        }

        return $this->getConversation($this->getConnection()->lastInsertId());
    }
}
