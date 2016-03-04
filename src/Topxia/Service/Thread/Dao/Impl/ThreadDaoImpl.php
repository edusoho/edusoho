<?php

namespace Topxia\Service\Thread\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Thread\Dao\ThreadDao;

class ThreadDaoImpl extends BaseDao implements ThreadDao
{
    protected $table = 'thread';

    private $serializeFields = array(
        'ats' => 'json'
    );

    public function getThread($id)
    {
        $that = $this;

        return $this->fetchCached('id:{$id}', $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $thread = $that->getConnection()->fetchAssoc($sql, array($id));

            return $thread ? $that->createSerializer()->unserialize($thread, $that->getSerializeFields()) : null;
        }

        );
    }

    public function findThreadsByTargetAndUserId($target, $userId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql     = "SELECT * FROM {$this->table} WHERE targetType = ? AND targetId = ? AND userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        $threads = $this->getConnection()->fetchAll($sql, array($target['type'], $target['id'], $userId)) ?: array();

        return $this->createSerializer()->unserializes($threads, $this->serializeFields);
    }

    public function findThreadsByTargetAndPostNum($target, $postNum, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql     = "SELECT * FROM {$this->table} WHERE targetType = ? AND targetId = ? AND postNum = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        $threads = $this->getConnection()->fetchAll($sql, array($target['type'], $target['id'], $postNum)) ?: array();

        return $this->createSerializer()->unserializes($threads, $this->serializeFields);
    }

    public function searchThreads($conditions, $orderBys, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createThreadSearchQueryBuilder($conditions)
                        ->select('*')
                        ->setFirstResult($start)
                        ->setMaxResults($limit);

        foreach ($orderBys as $orderBy) {
            $builder->addOrderBy($orderBy[0], $orderBy[1]);
        }

        $threads = $builder->execute()->fetchAll() ?: array();

        return $this->createSerializer()->unserializes($threads, $this->serializeFields);
    }

    public function searchThreadCount($conditions)
    {
        $builder = $this->createThreadSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    protected function createThreadSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, $this->table)
                        ->andWhere("updateTime >= :updateTime_GE")
                        ->andWhere("targetType = :targetType")
                        ->andWhere('targetId = :targetId')
                        ->andWhere('userId = :userId')
                        ->andWhere('type = :type')
                        ->andWhere('sticky = :isStick')
                        ->andWhere('nice = :nice')
                        ->andWhere('postNum = :postNum')
                        ->andWhere('postNum > :postNumLargerThan')
                        ->andWhere("status = :status")
                        ->andWhere("createdTime >= :startTime")
                        ->andWhere("createdTime <= :endTime")
                        ->andWhere('title LIKE :title')
                        ->andWhere('id NOT IN ( :excludeIds )')
                        ->andWhere('targetId IN (:targetIds)')
                        ->andWhere('startTime > :startTimeGreaterThan')
                        ->andWhere('content LIKE :content');

        return $builder;
    }

    public function addThread($fields)
    {
        $fields['createdTime'] = time();
        $fields['updateTime']  = $fields['createdTime'];

        $this->createSerializer()->serialize($fields, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $fields);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course thread error.');
        }

        return $this->getThread($this->getConnection()->lastInsertId());
    }

    public function updateThread($id, $fields)
    {
        $this->clearCached();
        $fields['updateTime'] = time();
        $this->createSerializer()->serialize($fields, $this->serializeFields);
        $fields['updateTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getThread($id);
    }

    public function deleteThread($id)
    {
        $this->clearCached();

        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function waveThread($id, $field, $diff)
    {
        $this->clearCached();
        $fields = array('postNum', 'hitNum', 'memberNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $currentTime = time();
        $sql         = "UPDATE {$this->table} SET {$field} = {$field} + ?, updateTime = {$currentTime} WHERE id = ? LIMIT 1";

        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getSerializeFields()
    {
        return $this->serializeFields;
    }
}
