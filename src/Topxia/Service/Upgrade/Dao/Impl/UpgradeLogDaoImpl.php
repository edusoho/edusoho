<?php

namespace Topxia\Service\Upgrade\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Upgrade\Dao\UpgradeLogDao;

class UpgradeLogDaoImpl extends BaseDao implements UpgradeLogDao 
{

    protected $table = 'upgrade_logs';

    public function getLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addLog($log)
    {
        $affected = $this->getConnection()->insert($this->table, $log);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert upgrade log error.');
        }
        return $this->getLog($this->getConnection()->lastInsertId());
    }

    public function updateLog($id,$log)
    {
        $this->getConnection()->update($this->table, $log, array('id' => $id));
        return $this->getInstalledPackage($id);
    }

    public function searchLogCount($conditions)
    {
        $builder = $this->createLogQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchLogs($conditions, $start, $limit)
    {
        $builder = $this->createLogQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function getUpdateLogByEnameAndVersion($ename,$version)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ename = ? AND version = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($ename, $version));
    }

    private function createLogQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'upgrade_logs')
            ->andWhere('remoteId = :remoteId')
            ->andWhere('ename = :ename')
            ->andWhere('cname = :cname')
            ->andWhere('dbBackPath LIKE :dbBackPath')
            ->andWhere('srcBackPath LIKE :srcBackPath')
            ->andWhere('status = :status');
    }
}