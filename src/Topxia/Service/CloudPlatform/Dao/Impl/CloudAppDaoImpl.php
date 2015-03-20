<?php

namespace Topxia\Service\CloudPlatform\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\CloudPlatform\Dao\CloudAppDao;

class CloudAppDaoImpl extends BaseDao implements CloudAppDao 
{
    protected $table = 'cloud_app';

    public function getApp($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getAppByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code)) ? : null;
    }

    public function findAppsByCodes(array $codes)
    {
        if (empty($codes)) { 
            return array(); 
        }

        $marks = str_repeat('?,', count($codes) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE code IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $codes);
    }

    public function findApps($start, $limit)
    {
         $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY installedTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql);       
    }

    public function findAppCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql);
    }

    public function addApp($App)
    {
        $affected = $this->getConnection()->insert($this->table, $App);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert App error.');
        }
        return $this->getApp($this->getConnection()->lastInsertId());
    }

    public function updateApp($id,$App)
    {
        $this->getConnection()->update($this->table, $App, array('id' => $id));
        return $this->getApp($id);
    }

	public function deleteApp($id)
	{
        return $this->getConnection()->delete($this->table, array('id' => $id));
	}

    public function updateAppVersion($code,$version)
    {
        $this->getConnection()->update($this->table, $version, array('code' => $code));
        return true;
    }
    
    public function updateAppFromVersion($code,$fromVersion)
    {
        $this->getConnection()->update($this->table, $fromVersion, array('code' => $code));
        return true; 
    }
}