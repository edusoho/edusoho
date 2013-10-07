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

	// 日志操作一般不设计更新,所以忽略update操作
}