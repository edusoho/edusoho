<?php
namespace Topxia\Service\IM\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\IM\Dao\ConversationDao;

class ConversationDaoImpl extends BaseDao implements ConversationDao
{
    protected $table = 'im_conversation';

    public $serializeFields = array(
        'memberIds' => 'saw'
    );

    public function getConversationByMemberIds(array $memberIds)
    {
        $conversation = array(
            'memberIds' => $memberIds
        );
        $conversation = $this->createSerializer()->serialize($conversation, $this->serializeFields);
        $sql          = "SELECT * FROM {$this->getTable()} where memberIds=? LIMIT 1";
        $conversation = $this->getConnection()->fetchAssoc($sql, array($conversation['memberIds']));
        return $conversation ? $this->createSerializer()->unserialize($conversation, $this->serializeFields) : null;
    }

    public function getConversationByConvNo($convNo)
    {
        $sql          = "SELECT * FROM {$this->getTable()} where no = ? LIMIT 1";
        $conversation = $this->getConnection()->fetchAssoc($sql, array($convNo));
        return $conversation ? $this->createSerializer()->unserialize($conversation, $this->serializeFields) : null;
    }

    public function getConversationByTargetIdAndTargetType($targetId, $targetType)
    {
        $sql          = "SELECT * FROM {$this->getTable()} WHERE targetId = ? AND targetType = ? LIMIT 1";
        $conversation = $this->getConnection()->fetchAssoc($sql, array($targetId, $targetType));
        return $conversation ? $this->createSerializer()->unserialize($conversation, $this->serializeFields) : null;
    }

    public function getConversationByMemberHash($memberHash)
    {
        $sql          = "SELECT * FROM {$this->getTable()} where memberHash=? LIMIT 1";
        $conversation = $this->getConnection()->fetchAssoc($sql, array($memberHash));
        return $conversation ? $this->createSerializer()->unserialize($conversation, $this->serializeFields) : null;
    }

    public function getConversation($id)
    {
        $sql          = "SELECT * FROM {$this->getTable()} where id=? LIMIT 1";
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

    public function deleteConversationByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->getConnection()->delete($this->table, array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function searchConversations($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchConversationCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)

            ->from($this->table, $this->table)
            ->andWhere('targetType IN (:targetTypes)')
            ->andWhere('targetId IN (:targetIds)')
            ->andWhere('convNo = :convNo');

        return $builder;
    }
}
