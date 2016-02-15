<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\IpBlacklistDao;

class IpBlacklistDaoImpl extends BaseDao implements IpBlacklistDao
{
    protected $table = 'ip_blacklist';

    public function getIp($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function getIpByIpAndType($ip, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ip = ? AND type =? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($ip, $type));
    }

    public function findIpsByTypeAndExpiredTimeLessThan($type, $time, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND expiredTime <= ? LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAssoc($sql, array($ip));
    }

    public function addIp($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert fields error.');
        }
        return $this->getIp($this->getConnection()->lastInsertId());
    }

    public function increaseIpCounter($id, $counter)
    {
        $counter = (int) $counter;
        $sql = "UPDATE {$this->table} SET counter = counter + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($counter, $id));
    }

    public function deleteIp($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }


}