<?php

namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\UpgradeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;
use Topxia\Service\Util\MySQLDumper;

class UpgradeServiceImpl extends BaseService implements UpgradeService
{
	private $fileCount=0;

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

//	public function getExtractFiles

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

	public function checkEnvironment()
	{
		$result = array();
		if(!class_exists('ZipArchive')){
 		   $result[] = "php-zip包未激活";
		}

		if(!function_exists('curl_init')){
 		   $result[] = "php-curl包未激活";
		}
		if (!$this->is_writable($this->getDownloadPath())){
			$result[] = '下载目录无写权限';
		}
		if (!$this->is_writable($this->getBackUpPath())){
			$result[] = '备份目录无写权限';
		}
		if(!$this->is_writable($this->getSystemRootPath().'app')){
			$result[] = 'app目录无写权限';
		}
		if(!$this->is_writable($this->getSystemRootPath().'src')){
			$result[] = 'src目录无写权限';
		}
		if(!$this->is_writable($this->getSystemRootPath().'web')){
			$result[] = 'web目录无写权限';
		}
		if(!$this->is_writable($this->getSystemRootPath().'app'.DIRECTORY_SEPARATOR.'cache')){
			$result[] = 'app/cache目录无写权限';
		}	


		return $result;
	}

	public function checkDepends($id)
	{
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

	public function downloadAndExtract($id)
	{
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

	public function backUpSystem($id)
	{
		$result = array();
		try{
			$package = $this->getEduSohoUpgradeService()->getPackage($id);
		 }catch(\Exception $e){
			$result[] = $e->getMessage();
			return $result;
		}
		$backupDbFile = $this->backUpDb($package);
		$backupFilesDirs = $this->backUpFiles($package);
	}

	public function beginUpgrade($id)
	{
		$result = array();
		try{
			$package = $this->getEduSohoUpgradeService()->getPackage($id);
		 }catch(\Exception $e){
			$result[] = $e->getMessage();
			return $result;
		}
		
		try{
			$deletes = $this->getExtractPath($package).DIRECTORY_SEPARATOR.'delete';
			$this->deleteFiles($deletes);
		}catch(\Exception $e){
			$result[]= "当前总共升级了{$this->fileCount}个文件，升级文件无法覆盖，原因: {$e->getMessage()}";
			return $result;
		}
	
		$source = $this->getExtractPath($package).DIRECTORY_SEPARATOR.'source'.DIRECTORY_SEPARATOR;
		try{
			$this->deepCopy($source,$this->getSystemRootPath());
		}catch(\Exception $e){
			$result[]= "当前总共升级了{$this->fileCount}个文件，升级文件无法覆盖，原因: {$e->getMessage()}";
			return $result;
		}

		$upgradeFile = $this->getExtractPath($package).DIRECTORY_SEPARATOR.'Upgrade.php';
		if(!file_exists($upgradeFile)) return $result;
		include_once($upgradeFile);
		$upgrade = new \Upgrade($kernel);
		if(method_exists($upgrade, 'update')){
			$upgrade->update();
		}
	}

	private function deleteFiles($deletes){
		if(!file_exists($deletes)) return ;
		$fh = fopen($deletes,'r');
		$this->fileCount = 0;
		while ($line = fgets($fh)) {
  			unlink($this->getSystemRootPath().DIRECTORY_SEPARATOR.$line);
  			$this->fileCount ++;
		}
		fclose($fh);		
	}

	private function  is_writable($path) {
	    $path .= DIRECTORY_SEPARATOR.uniqid(mt_rand()).'.tmp';
	    $rm = file_exists($path);
	    $f = @fopen($path, 'a');
	    if ($f===false)
	        return false;
	    fclose($f);
	    if (!$rm)
	        unlink($path);
	    return true;
	}

	private function backUpFiles($package)
	{
		$backupFilesDirs = array();
		$backUpdir = $this->getPackageBackUpDir($package);
		$backupFilesDirs['src'] = $this->getPackageBackUpDir($package).'src';
		$backupFilesDirs['app'] = $this->getPackageBackUpDir($package).'app';
		$backupFilesDirs['web'] = $this->getPackageBackUpDir($package).'web';

		$this->deepCopy($this->getSystemRootPath().'src',$backupFilesDirs['src']);
		$this->deepCopy($this->getSystemRootPath().'app',$backupFilesDirs['app'],$this->getFilters());
		$this->deepCopy($this->getSystemRootPath().'web',$backupFilesDirs['web']);
		return $backupFilesDirs;	
	}

	private function getFilters()
	{
		return array('app'.DIRECTORY_SEPARATOR.'data',
			   'app'.DIRECTORY_SEPARATOR.'cache',
			   'app'.DIRECTORY_SEPARATOR.'logs',
			   'web'.DIRECTORY_SEPARATOR.'files'
		);
	}



 	private function deepCopy($src,$dest,$filters=array())
 	{
		if(!file_exists($src)) return ;
		if(!file_exists($dest)){
			mkdir($dest,0777,true);
		}
		foreach(new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
			 \RecursiveIteratorIterator::SELF_FIRST ) as $path) {
			if($this->patternMatch($path->getPathname(),$filters)){
					continue;
			}
			$relativeFile = str_replace($src,'',$path->getPathname());

			$destFile = $dest.$relativeFile;	
			if($path->isDir() ){
				if(!file_exists($destFile))
					mkdir($destFile,0777,true);
			}else{
				if(strpos( $path->getFilename(), ".") ===0 ){
					continue;
				}
				copy($path->getPathname(),$destFile);
				$this->fileCount ++;
			}
		}
 	}

 	private function patternMatch($path,&$filters)
 	{
 		foreach ($filters as $filter) {
 			if(!(strpos($path,$filter)===false)){
 				return true;
 			}
 		}
 		return false;
 	}

	private function backUpDb($package)
	{
		$dbSetting = array('exclude'=>array('session','cache'));
		$dump = new MySQLDumper($this->getKernel()->getConnection());
		$date = date('YmdHis');
		$backUpdir = $this->getPackageBackUpDir($package);
		$this->emptyDir($backUpdir);
		return 	$dump->export($backUpdir.$date);	
	}

	private function getPackageBackUpDir($package){
		if(isset($package['type']) && $package['type']==1){
			$dir = $this->getBackUpPath().DIRECTORY_SEPARATOR;
			$dir .= 'upgrade_'.$package['ename'].'_'.$package['fromVersion'];
			$dir .= DIRECTORY_SEPARATOR;
		}else{
			$dir = $this->getBackUpPath().DIRECTORY_SEPARATOR;
			$dir .= 'install_'.$package['ename'].'_'.$package['version'];			
			$dir .= DIRECTORY_SEPARATOR;
		}
		if(!file_exists($dir)){
			mkdir($dir,0777,true);
		}
		return $dir;

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

		$dir = $this->getDownloadPath();
		$extractPath = $dir.DIRECTORY_SEPARATOR.basename($path, ".zip");
		$this->emptyDir($extractPath);
		$zip = new \ZipArchive;
		if ($zip->open($path) === TRUE) {
    		$zip->extractTo($dir);
    		$zip->close();
		} else {
    		throw new \Exception('无法解压缩安装包！');
		}
		return $extractPath;
	}
	

	
	private function emptyDir($dirPath,$includeDir=false){
		if(!file_exists($dirPath)) return ;
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
    		$path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
		}
		if($includeDir){
			rmdir($dirPath);
		}
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

	private function getExtractPath($package)
	{
    	return $this->getDownloadPath().
    			DIRECTORY_SEPARATOR.basename($package['filename'], ".zip"); 	
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