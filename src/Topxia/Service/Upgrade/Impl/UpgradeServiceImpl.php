<?php

namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\UpgradeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class UpgradeServiceImpl extends BaseService implements UpgradeService
{

	public function check($packages,$clientInfo)
	{
		$packageNames = ArrayToolkit::parts($packages);
		$canInstallPackages = $this->getPackageDao()->findPackagesByTypeAndNotIncluded($packageNames, UpgradeService::PackTypeForInstall);
		$canUpdatePackages = array();
		foreach ($packages as $package) {
			$row = $this->getPackageDao()->
		  		getPackageByPackTypeAndFromVersionAndEname(UpgradeService::PackTypeForUpgrade , $package['version'], $package['ename']);
		    if(!empty($row)){
		  		$canUpdatePackages[] = $row;
		    }
		}
		$result = array_merge($canInstallPackages,$canUpdatePackages);
		$this->addCheckLog($packages,$clientInfo);
		return $result;
	}
	
	public function upgrade($package)
	{

	}
	public function install($package)
	{

	}

	public function addPackage($package)
	{
		return $this->getPackageDao()->addPackage($package);

	}
	public function deletePackage($id)
	{
		return $this->getPackageDao()->deletePackage($id);

	}
	public function updatePackage($id,$package)
	{
		return $this->getPackageDao()->updatePackage($id,$package);
	}

	private function addCheckLog($packages,$clientInfo)
	{
		$log = array('operation' =>'check' ,
		             'packages'=> serialize($packages),
		             'ip'=> $clientInfo['ip'],
		             'host' =>  $clientInfo['host'],
		             'logtime'=> time());
		$this->getUpgradeLogDao()->addLog($log);
	}

    private function getPackageDao ()
    {
        return $this->createDao('Upgrade.PackageDao');
    }

    private function getUpgradeLogDao ()
    {
        return $this->createDao('Upgrade.UpgradeLogDao');
    }	

     private function getInstalledPackageDao ()
    {
        return $this->createDao('Upgrade.InstalledPackageDao');
    }	
}