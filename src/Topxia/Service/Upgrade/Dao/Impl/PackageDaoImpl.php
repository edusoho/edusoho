<?php

namespace Topxia\Service\Upgrade\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Upgrade\Dao\PackageDao;

class PackageDaoImpl extends BaseDao implements PackageDao 
{
	protected $table = 'packages';

	public function getPackage($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addPackage($package)
    {
        $affected = $this->getConnection()->insert($this->table, $package);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Upgrade Package error.');
        }
        return $this->getPackage($this->getConnection()->lastInsertId());
    }

	public function updatePackage($id,$package)
	{
		$this->getConnection()->update($this->table, $package, array('id' => $id));
        return $this->getPackage($id);
	}

	public function deletePackage($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function findPackagesByTypeAndNotIncluded(array $packagenames, $packType)
	{
		if(empty($packagenames)){ return array(); }
        $marks = str_repeat('?,', count($packagenames) - 1) . '?';
        $marks = mysql_real_escape_string($marks);
        $sql ="SELECT * FROM {$this->table} WHERE ename NOT IN ({$marks}) AND packType = ? ;";
        return $this->getConnection()->fetchAll($sql, $ids, $packType);
	}

	public function getPackageByPackTypeAndFromVersionAndEname($packType, $fromVersion, $ename)
	{
		$sql = "SELECT * FROM {$this->table} WHERE packType = ? AND fromVersion = ? AND ename = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($packType, $fromVersion, $ename));
	}

}