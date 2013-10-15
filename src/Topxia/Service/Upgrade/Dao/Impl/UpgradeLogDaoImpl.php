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
        
    }

    public function searchLogs($conditions, $start, $limit)
    {

       
    }
}