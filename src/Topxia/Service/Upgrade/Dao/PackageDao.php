<?php

namespace Topxia\Service\Upgrade\Dao;

interface PackageDao 
{
	public function getPackage($id);

	public function addPackage($package);

	public function updatePackage($id,$package);

	public function deletePackage($id);

	// query

	// 过滤软件包
	public function findPackagesByTypeAndNotIncluded(array $packagenames);

	public function getPackageByPackTypeAndFromVersionAndEname($ename,$fromVersion);

}
