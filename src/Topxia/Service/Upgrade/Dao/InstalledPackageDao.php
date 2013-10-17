<?php

namespace Topxia\Service\Upgrade\Dao;

interface InstalledPackageDao 
{
	
	public function getInstalledPackage($id);

	public function getInstalledPackageByEname($ename);

	public function addInstalledPackage($installedPackage);

	public function updateInstalledPackage($id,$installedPackage);

	public function deleteInstalledPackage($id);

	public function findInstalledPackages();

	public function searchPackageCount($conditions);

	public function searchPackages($conditions, $start, $limit);
}