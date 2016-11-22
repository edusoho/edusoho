<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\IpBlacklistDao;

class IpBlacklistDaoImpl extends BaseDao implements IpBlacklistDao
{
    protected $table = 'ip_blacklist';

    public function getIp($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id));
        });
    }

    public function getIpByIpAndType($ip, $type)
    {
        $that = $this;

        return $this->fetchCached("ip:{$ip}:type:{$type}", $ip, $type, function ($ip, $type) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE ip = ? AND type =? LIMIT 1";
            $result = $that->getConnection()->fetchAssoc($sql, array($ip, $type));
            return $result ? $result : array();
        });
    }

    public function findIpsByTypeAndExpiredTimeLessThan($type, $time, $start, $limit)
    {
        $that = $this;

        return $this->fetchCached("type:{$type}:time:{$time}:start:{$start}:limit:{$limit}", $type, $time, $start, $limit, function ($type, $time, $start, $limit) use ($that) {
            $this->filterStartLimit($start, $limit);
            $sql = "SELECT * FROM {$that->getTable()} WHERE type = ? AND expiredTime <= ? LIMIT {$start}, {$limit}";
            return $that->getConnection()->fetchAssoc($sql, array($ip));
        });
    }

    public function addIp($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert fields error.');
        }
        $this->clearCached();
        return $this->getIp($this->getConnection()->lastInsertId());
    }

    public function increaseIpCounter($id, $counter)
    {
        $counter = (int) $counter;
        $sql = "UPDATE {$this->table} SET counter = counter + ? WHERE id = ? LIMIT 1";
        $result = $this->getConnection()->executeQuery($sql, array($counter, $id));
        $this->clearCached();
        return $result;
    }

    public function deleteIp($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }


}