<?php

namespace Topxia\Service\Crontab\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Crontab\Dao\JobDao;

class JobDaoImpl extends BaseDao implements JobDao
{
    protected $table = 'crontab_job';

    private $serializeFields = array(
        'jobParams' => 'json'
    );

    public function getJob($id, $lock = false)
    {
        $forUpdate = $lock ? "FOR UPDATE" : "";
        $sql       = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1 {$forUpdate}";
        $job       = $this->getConnection()->fetchAssoc($sql, array($id));
        return $job ? $this->createSerializer()->unserialize($job, $this->getSerializeFields()) : null;
    }

    public function searchJobs($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $threads = $builder->execute()->fetchAll() ?: array();
        return $this->createSerializer()->unserializes($threads, $this->serializeFields);
    }

    public function searchJobsCount($conditions)
    {
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addJob($job)
    {
        $this->createSerializer()->serialize($job, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $job);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert job error.');
        }
        return $this->getJob($this->getConnection()->lastInsertId());
    }

    public function updateJob($id, $fields)
    {
        $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getJob($id);
    }

    public function deleteJob($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteJobs($targetId, $targetType)
    {
        return $this->getConnection()->delete($this->table, array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function findJobByTargetTypeAndTargetId($targetType, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE targetType = ? AND targetId = ?";
        $job = $this->getConnection()->fetchAll($sql, array($targetType, $targetId)) ?: array();
        return $this->createSerializer()->unserialize($job, $this->serializeFields);
    }

    public function findJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND targetType = ? AND targetId = ?";
        $job = $this->getConnection()->fetchAssoc($sql, array($jobName, $targetType, $targetId)) ?: array();
        return $this->createSerializer()->unserialize($job, $this->serializeFields);
    }

    protected function createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere("name LIKE :name")
            ->andWhere("cycle = :cycle")
            ->andWhere('jobClass = :jobClass')
            ->andWhere('executing = :executing')
            ->andWhere('nextExcutedTime <= :nextExcutedTime')
            ->andWhere('nextExcutedTime <= :nextExcutedEndTime')
            ->andWhere('nextExcutedTime >= :nextExcutedStartTime')
            ->andWhere('creatorId = :creatorId');
        return $builder;
    }

    protected function getSerializeFields()
    {
        return $this->serializeFields;
    }
}
