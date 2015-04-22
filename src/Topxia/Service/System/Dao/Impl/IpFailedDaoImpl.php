<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\IpFailed;

class IpFailedImpl extends BaseDao implements IpFailed
{
    protected $table = 'ip_failed';

    public function getIp($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function getIpByIp($ip)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ip = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($ip));
    }

    public function findIpsByExpiredTimeLessThan($time, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE expiredTime <= ? LIMIT {$start}, {$limit}";
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

    public function increaseIpCounter($id, $diff)
    {
        $counter = (int) $counter;
        $sql = "UPDATE {$this->table} SET counter = counter + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function deleteIp($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }


}