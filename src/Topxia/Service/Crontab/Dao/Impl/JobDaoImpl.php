<?php

namespace Topxia\Service\Crontab\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Crontab\Dao\JobDao;

class JobDaoImpl extends BaseDao implements JobDao 
{
    protected $table = 'crontab_job';

    public function getJob($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function searchJobs($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $threads = $builder->execute()->fetchAll() ? : array();
        return $this->createSerializer()->unserializes($threads, $this->serializeFields);
    }

    public function searchJobsCount($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addJob($task)
    {
        $affected = $this->getConnection()->insert($this->table, $task);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert task error.');
        }
        return $this->getJob($this->getConnection()->lastInsertId());
    }

    public function updateJob($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getJob($id);
    }

    private function createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere("cycle = :cycle")
            ->andWhere('jobClass = :jobClass')
            ->andWhere('executing = :executing')
            ->andWhere('nextExcutedTime <= :nextExcutedTime')
            ->andWhere('creatorId = :creatorId');
        return $builder;
    }

}