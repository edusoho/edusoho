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

    public function addInstalledPackage($installedPackage)
    {
        $affected = $this->getConnection()->insert($this->table, $installedPackage);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert installedPackage error.');
        }
        return $this->getInstalledPackage($this->getConnection()->lastInsertId());
    }

	public function deleteInstalledPackage($id)
	{
        return $this->getConnection()->delete($this->table, array('id' => $id));
	}
	
}