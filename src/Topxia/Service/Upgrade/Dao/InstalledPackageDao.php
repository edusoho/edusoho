<?php

namespace Topxia\Service\Upgrade\Dao;

interface InstalledPackageDao 
{
	
	public function getInstalledPackage($id);

	public function addInstalledPackage($installedPackage);

	public function deleteInstalledPackage($id);

	// 日志操作一般不设计更新,所以忽略update操作
}