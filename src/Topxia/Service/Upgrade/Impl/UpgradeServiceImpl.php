<?php

namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\UpgradeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;

class UpgradeServiceImpl extends BaseService implements UpgradeService
{
	public function addInstalledPackage($packageInfo)
	{
		$installedPackage = array();
		$installedPackage['ename'] = $packageInfo['ename'];
		$installedPackage['cname'] = $packageInfo['cname'];
		$installedPackage['version'] = $packageInfo['version'];
		$installedPackage['fromVersion'] = $packageInfo['fromVersion'];
		$installedPackage['installlog'] = 'result-success';
		$installedPackage['installTime'] = time();
		$existPackage = $this->getInstalledPackageDao()->getInstalledPackageByEname($packageInfo['ename']);
		if(empty($existPackage)){
			return $this->getInstalledPackageDao()->addInstalledPackage($installedPackage);
		} else {
			return $this->getInstalledPackageDao()->updateInstalledPackage($existPackage['id'], 
				$installedPackage);
		}
	}

	public function getRemoteInstallPackageInfo($id)
	{
		$package = $this->getEduSohoUpgradeService()->install($id);
		$package = (array)$package;
		return $package;
	}

	public function getRemoteUpgradePackageInfo($id)
	{
		$package = $this->getEduSohoUpgradeService()->upgrade($id);
		$package = (array)$package;
		return $package;
	}

	public function searchPackageCount()
	{
		return $this->getInstalledPackageDao()->searchPackageCount();
	}

	public function searchPackages($start, $limit)
	{
		return $this->getInstalledPackageDao()->findPackages($start, $limit);
	}

	public function check()
	{
		$packages = $this->getInstalledPackageDao()->findInstalledPackages();
		if(!$this->checkMainVersion($packages)){
			$packages =$this->addMainVersionAndReloadPackages();
		}
		
		$packagesToUpgrade =  $this->getEduSohoUpgradeService()->check($packages);
		$packagesToUpgradeOfArray = array();

		if(empty($packagesToUpgrade)){
			return array();
		} else {
			foreach ($packagesToUpgrade as &$packageToUpgrade) {
				$packageToUpgrade = (array)$packageToUpgrade;
				$packageToUpgrade['comments'] = strip_tags($packageToUpgrade['comments']);
				$packagesToUpgradeOfArray[] = $packageToUpgrade;
			}

			return $packagesToUpgradeOfArray;
		}
		
	}

	public function install($id)
	{
		$package = $this->getEduSohoUpgradeService()->install($id);
		$package = (array)$package;
		$path = $this->getEduSohoUpgradeService()->downloadPackage($package['uri'],$package['filename']);
		$dirPath = $this->extractFile($path);
	}

	public function upgrade($id)
	{
		$package = $this->getEduSohoUpgradeService()->upgrade($id);
		$package = (array)$package;
		$result = $this->checkDepends($package['depends']);
		if(!empty($result)){
			return $result;
		}
		$path = $this->getEduSohoUpgradeService()->downloadPackage($package['uri'],$package['filename']);
		$dirPath = $this->extractFile($path);

	}

	private function extractFile($path)
	{
		$dir = $this->getKernel()->getParameter('topxia.disk.upgrade_dir');
		$extractDir = $dir.DIRECTORY_SEPARATOR.basename($path, ".zip");
		$zip = new \ZipArchive;
		if ($zip->open($path) === TRUE) {
    		$zip->extractTo($extractDir);
    		$zip->close();
		} else {
    		throw new \Exception('无法解压缩安装包！');
		}	
	}
	

	private function checkDepends($depends)
	{
		if(empty($depends)){
			return '';
		}
		$message = '';
		foreach ($depends as $key => $depend) {
			$installed = $this->getInstalledPackageDao()->getInstalledPackageByEname($key);
			if(empty($installed)){
				$message.= " 没有安装 {$depend['o']} {$depend['v']} 版本的{$depend['cname']} \n";
				continue;
			}
			if(!version_compare($installed['version'],$depend['v'],$depend['o'])){
				$message.= " 依赖 {$depend['o']} {$depend['v']} 版本的{$depend['cname']}，而当前版本为:{$installed['version']} \n";
			}
		}
		if(!is_writable($this->getContainer()->getParameter('topxia.disk.upgrade_dir'))){
			$downloadPath = $this->getContainer()->getParameter('topxia.disk.upgrade_dir');
			$message.= " 路径:{$downloadPath} 无法写数据，权限不足! \n";
		}
		return $message;
	}

	private function checkMainVersion($packages)
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
			'installTime' => 0
			);
		$this->getInstalledPackageDao()->addInstalledPackage($mainPackage);
		return $this->getInstalledPackageDao()->findInstalledPackages();
	}

    private function getInstalledPackageDao ()
    {
        return $this->createDao('Upgrade.InstalledPackageDao');
    }	

    private function getEduSohoUpgradeService ()
    {
        return $this->createService('Upgrade.EduSohoUpgradeService');
    }	
}