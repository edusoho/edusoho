<?php

namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\UpgradeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;

class UpgradeServiceImpl extends BaseService implements UpgradeService
{
	public function backUpdirectories($directory)
	{
		$directoryConf = $directory.'/directories2Backup.conf';
		$content = file_get_contents($directoryConf);
		$directories2Backup = array_keys((array)json_decode($content));
		
		foreach ($directories2Backup as $directory2Backup) {
			$this->backUpDirectory($directory2Backup);
		}

		return true;
	}

	private function backUpDirectory($directory2Backup)
	{
		$destinationPath = '/var/www/edusoho/'.$directory2Backup.time().'.zip';
		$this->Zip($directory2Backup, $destinationPath, true);
		$fileRecord = $this->getFileService()->uploadFile('upgradeBackup', new File($destinationPath));
	}

	public function addInstalledPackage($packageInfo)
	{
		$installedPackage = array();
		$installedPackage['ename'] = $packageInfo['ename'];
		$installedPackage['cname'] = $packageInfo['cname'];
		$installedPackage['version'] = $packageInfo['version'];
		$installedPackage['from'] = $packageInfo['fromVersion'];
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
		return $package;
	}

	public function getRemoteUpgradePackageInfo($id)
	{
		$package = $this->getEduSohoUpgradeService()->upgrade($id);
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
		
		return $this->getEduSohoUpgradeService()->check($packages);

	}

	public function install($id)
	{
		$this->upgrade($id);
	}

	public function checkEnvironment(){
		$result = array();
		if (!is_writable($this->getDownloadPath())){
			$result[] = '下载目录无写权限';
		}
		if (!is_writable($this->getBackUpPath())){
			$result[] = '备份目录无写权限';
		}
		if(!is_writable($this->getSystemRootPath().'app')){
			$result[] = 'app目录无写权限';
		}
		if(!is_writable($this->getSystemRootPath().'src')){
			$result[] = 'src目录无写权限';
		}
		if(!is_writable($this->getSystemRootPath().'web')){
			$result[] = 'web目录无写权限';
		}
		if(!is_writable($this->getSystemRootPath().'app'.DIRECTORY_SEPARATOR.'cache')){
			$result[] = 'app/cache目录无写权限';
		}		
		return $result;
	}

	public function checkDepends($id){
		$result = array();
		try{
			$package = $this->getEduSohoUpgradeService()->getPackage($id);
		 }catch(\Exception $e){
			$result[] = $e->getMessage();
			return $result;
		}

		$depends = $package['depends'];	

		if(empty($depends)){
			return $result;
		}
		foreach ($depends as $key => $depend) {
			$installed = $this->getInstalledPackageDao()->getInstalledPackageByEname($key);
			if(empty($installed)){
				$result[]= " 没有安装 {$depend['o']} {$depend['v']} 版本的{$depend['cname']} \n";
				continue;
			}
			if(!version_compare($installed['version'],$depend['v'],$depend['o'])){
				$result[]=  " 该安装包依赖 {$depend['o']} {$depend['v']} 版本的{$depend['cname']}，而当前版本为:{$installed['version']} \n";
			}
		}
		return $result;			
	}

	public function downloadAndExtract($id){
		$result = array();
		try{
			$package = $this->getEduSohoUpgradeService()->getPackage($id);
			$path = $this->getEduSohoUpgradeService()->downloadPackage($package['uri'],$package['filename']);
			$dirPath = $this->extractFile($path);		
	    }catch(\Exception $e){
	    	$result[] = $e->getMessage();
	    }
	    return $result;
	}

	public function backUpSystem($id){
		$result = array();
		try{
			$package = $this->getEduSohoUpgradeService()->getPackage($id);
		 }catch(\Exception $e){
			$result[] = $e->getMessage();
			return $result;
		}	
		//TODO backDatabase
		//TODO backFiles;

	}


	public function upgrade($id)
	{

		

		//TODO 
		//  1、备份
		//      1.1 备份数据库； 1.2 备份文件
		//  2、覆盖
		//  3、执行UPDATE.PHP
		//	   之中发生任何异常, 异常，先恢复数据库，然后再恢复文件
		//  4、删除cache
		//$this->backUpOldFiles($dirPath);





		return $result;

	}

	

	// private function checkUpgradeFilesPermisson($dirPath)
	// {
	// 	if(!file_exists($dirPath)) return '';

	// 	$dirPath .= DIRECTORY_SEPARATOR.'source';
	// 	$message = '';
	// 	foreach(new \RecursiveIteratorIterator(
	// 		new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS),
	// 		 \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
	// 		if($path->isFile() && $path->getFilename()!='.DS_Store'){

	// 			$fullPath = $path->getPathname();
	// 			$realPath = $this->getKernel()->getParameter('kernel.root_dir');
	// 			$realPath .= DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
	// 			$realFile = $realPath.str_replace($dirPath,'',$fullPath);
	// 			if(!is_writable($realFile)){
	// 				$relativePath = str_replace($dirPath,'',$fullPath);
	// 				$message .= '{$relativePath} \n';
	// 			}

	// 		}

	// 	}
	// 	if(!empty($message)) return '以下文件不可写 \n' .  $message;
	// 	return '';
	// }





	private function extractFile($path)
	{
		if(!class_exists('ZipArchive')){
 		   throw new Exception("Php5-zip包未安装");
		}
		$dir = $this->getDownloadPath();
		$extractPath = $dir.DIRECTORY_SEPARATOR.basename($path, ".zip");
		$this->deleteDir($extractPath);
		$zip = new \ZipArchive;
		if ($zip->open($path) === TRUE) {
    		$zip->extractTo($dir);
    		$zip->close();
		} else {
    		throw new \Exception('无法解压缩安装包！');
		}
		return $extractPath;
	}
	

	
	private function deleteDir($dirPath){
		if(!file_exists($dirPath)) return ;
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
    		$path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
		}
		rmdir($dirPath);
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

	private function getDownloadPath()
	{
		return $this->getKernel()->getParameter('topxia.disk.upgrade_dir');
	}

	private function getBackUpPath()
	{
		return $this->getKernel()->getParameter('topxia.disk.backup_dir');
	}	

	private function getSystemRootPath()
	{
		$realPath = $this->getKernel()->getParameter('kernel.root_dir');
		$realPath .= DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
		return $realPath;
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