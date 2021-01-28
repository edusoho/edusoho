<?php

namespace Biz\CloudPlatform\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\CloudPlatform\Dao\CloudAppLogDao;

class CloudAppLogDaoImpl extends GeneralDaoImpl implements CloudAppLogDao
{
    protected $table = 'cloud_app_logs';

    public function getLastLogByCodeAndToVersion($code, $toVersion)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? AND toVersion = ? ORDER BY createdTime DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($code, $toVersion));
    }

    public function find($start, $limit)
    {
        return $this->search(array(), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function countLogs()
    {
        return $this->count(array());
    }

    public function declares()
    {
        return array(
            'orderbys' => array(
                'createdTime',
            ),
        );
    }
}
