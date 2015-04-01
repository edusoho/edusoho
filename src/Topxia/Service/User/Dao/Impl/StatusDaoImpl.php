<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\StatusDao;

class StatusDaoImpl extends BaseDao implements StatusDao
{
    protected $table = 'status';

    private $serializeFields = array(
        'properties' => 'json',
    );

    public function getStatus($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $status = $this->getConnection()->fetchAssoc($sql, array($id));
        return $status ? $this->createSerializer()->unserialize($status, $this->serializeFields) : null;
    }

    public function searchStatusesCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function findStatusesByUserIds($userIds, $start, $limit)
    {
        if(empty($userIds)){
            return array();
        }
        $marks = str_repeat('?,', count($userIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE userId IN ({$marks});";
        $statuses = $this->getConnection()->fetchAll($sql, $userIds);
        return $this->createSerializer()->unserializes($statuses, $this->serializeFields);
    }

    public function findStatusesByUserIdsCount($userIds)
    {
        if(empty($userIds)){
            return array();
        }
        $marks = str_repeat('?,', count($userIds) - 1) . '?';
        $sql ="SELECT COUNT(*) FROM {$this->table} WHERE userId IN ({$marks});";

        $this->getConnection()->fetchColumn($sql, $userIds);
    }

    public function searchStatuses($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        $statuses = $builder->execute()->fetchAll() ? : array();

        return $this->createSerializer()->unserializes($statuses, $this->serializeFields);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('private = :private');
    }

    public function addStatus($fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert status error.');
        }
        return $this->getStatus($this->getConnection()->lastInsertId());
    }

    public function updateStatus($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getStatus($id);
    }

    public function deleteStatus($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
    
    public function deleteStatusesByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId)
    {
        return $this->getConnection()->delete($this->table, array(
            'userId' => $userId,
            'type' =>$type,
            'objectType'=>$objectType,
            'objectId'=>$objectId
            ));
    }
}