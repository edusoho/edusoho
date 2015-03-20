<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\MessageDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class MessageDaoImpl extends BaseDao implements MessageDao
{
    protected $table = 'message';

    public function getMessage($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addMessage($message)
    {
        $affected = $this->getConnection()->insert($this->table, $message);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert message error.');
        }
        return $this->getMessage($this->getConnection()->lastInsertId());
    }

    public function deleteMessage($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    private function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'message')
            ->andWhere('fromId = :fromId')
            ->andWhere('toId = :toId')
            ->andWhere('createdTime = :createdTime')
            ->andWhere('createdTime >= :startDate')
            ->andWhere('createdTime < :endDate')
            ->andWhere('content LIKE :content');
    }

    public function searchMessagesCount($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchMessages($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('createdTime', 'DESC');

        return $builder->execute()->fetchAll() ? : array();
    }

    public function getMessageByFromIdAndToId($fromId, $toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? AND toId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($fromId, $toId));
    }

    public function findMessagesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function deleteMessagesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

}