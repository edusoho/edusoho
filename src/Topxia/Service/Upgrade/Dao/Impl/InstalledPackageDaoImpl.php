<?php

namespace Topxia\Service\Upgrade\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Upgrade\Dao\InstalledPackageDao;

class InstalledPackageDaoImpl extends BaseDao implements InstalledPackageDao 
{
    
    protected $table = 'installed_packages';

    public function getInstalledPackage($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findPackages($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table}  ORDER BY installTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }
    
    public function searchPackageCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function addInstalledPackage($installedPackage)
    {
        $affected = $this->getConnection()->insert($this->table, $installedPackage);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert installedPackage error.');
        }
        return $this->getInstalledPackage($this->getConnection()->lastInsertId());
    }

    public function updateInstalledPackage($id,$installedPackage)
    {
        $this->getConnection()->update($this->table, $installedPackage, array('id' => $id));
        return $this->getInstalledPackage($id);
    }


	public function deleteInstalledPackage($id)
	{
        return $this->getConnection()->delete($this->table, array('id' => $id));
	}

    public function findInstalledPackages()
    {
        $sql = "SELECT ename,version FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql);       
    }

    public function getInstalledPackageByEname($ename)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ename = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($ename)) ? : null;
    }

	
}