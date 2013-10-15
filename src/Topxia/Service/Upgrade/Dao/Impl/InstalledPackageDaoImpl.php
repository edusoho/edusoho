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

    public function searchPackages($conditions, $start, $limit)
    {
        $builder = $this->createPackageQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();  
    }
    
    public function searchPackageCount($conditions)
    {
        $builder = $this->createPackageQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createPackageQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'installed_packages')
            ->andWhere('cname LIKE :cname')
            ->andWhere('ename LIKE :ename')
            ->andWhere('nickname LIKE :nickname')
            ->andWhere('version = :version')
            ->andWhere('fromVersion = :fromVersion');
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