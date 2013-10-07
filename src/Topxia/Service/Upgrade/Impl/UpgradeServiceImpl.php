<?php

namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\UpgradeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;

class UpgradeServiceImpl extends BaseService implements UpgradeService
{

	public function check()
	{
		$packages = $this->getInstalledPackageDao()->findInstalledPackages();
		if(!$this->checkMainVersion($packages)){

		}
	}

	public function upgrade($id)
	{

	}

	public function install($id)
	{

	}

	private function checkMainVersion(&$packages)
	{
		foreach ($packages as $package) {
			if('MAIN' == $package['ename']){
				return true;
			}
		}
		return false;
	}

	private function addMainVersionAndReloadPackages(){
		$mainPackage = array(
			'ename' => 'MAIN',
			'cname' => '系统版本',
			'version' => System::VERSION,
			'installTime' => time()
			);
	}

    private function getInstalledPackageDao ()
    {
        return $this->createDao('Upgrade.InstalledPackageDao');
    }	
}