<?php

namespace Topxia\Service\CloudPlatform\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\CloudPlatform\Dao\AppLogDao;

class CloudAppLogDaoImpl extends BaseDao implements AppLogDao 
{

    protected $table = 'cloud_app_logs';

    public function getLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getLastLogByCodeAndToVersion($code, $toVersion)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? AND toVersion = ? ORDER BY createdTime DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code, $toVersion));
    }

    public function findLogs($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql); 
    }

    public function findLogCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql);
    }

    public function addLog($log)
    {
        $affected = $this->getConnection()->insert($this->table, $log);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert app log error.');
        }
        return $this->getLog($this->getConnection()->lastInsertId());
    }

}