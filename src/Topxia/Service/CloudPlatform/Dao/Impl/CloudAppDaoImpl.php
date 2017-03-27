<?php

namespace Topxia\Service\CloudPlatform\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\CloudPlatform\Dao\CloudAppDao;

class CloudAppDaoImpl extends BaseDao implements CloudAppDao 
{
    protected $table = 'cloud_app';

    public function getApp($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        });
    }

    public function getAppByCode($code)
    {
        $that = $this;

        return $this->fetchCached("code:{$code}", $code, function ($code) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE code = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($code)) ? : null;
        });
    }

    public function findAppsByCodes(array $codes)
    {
        $codes = array_unique($codes);

        if (empty($codes)) { 
            return array(); 
        }

        $that = $this;
        sort($codes);
        $key = 'codes:'.implode("-", $codes);
        return $this->fetchCached($key, $codes, function ($codes) use ($that) {
            $marks = str_repeat('?,', count($codes) - 1) . '?';
            $sql ="SELECT * FROM {$that->getTable()} WHERE code IN ({$marks});";

            return $that->getConnection()->fetchAll($sql, $codes);
        });
    }

    public function findApps($start, $limit)
    {
        $that = $this;
        $this->filterStartLimit($start, $limit);

        return $this->fetchCached("apps:{$start}:{$limit}", $start, $limit, function ($start, $limit) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} ORDER BY installedTime DESC LIMIT {$start}, {$limit}";
            return $that->getConnection()->fetchAll($sql);       
        });
    }

    public function findAppCount()
    {
        $that = $this;

        return $this->fetchCached("count", function () use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()}";
            return $that->getConnection()->fetchColumn($sql);
        });
    }

    public function addApp($app)
    {
        $affected = $this->getConnection()->insert($this->table, $app);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert App error.');
        }
        $this->clearCached();
        return $this->getApp($this->getConnection()->lastInsertId());
    }

    public function updateApp($id,$app)
    {
        $this->getConnection()->update($this->table, $app, array('id' => $id));
        $this->clearCached();
        return $this->getApp($id);
    }

	public function deleteApp($id)
	{
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
	}
}