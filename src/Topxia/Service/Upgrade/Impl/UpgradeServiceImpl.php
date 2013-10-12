<?php

namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\UpgradeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;
use Topxia\Service\Util\MySQLDumper;

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

	public function checkEnvironment()
	{
		$result = array();
		if(!class_exists('ZipArchive')){
 		   $result[] = "php-zip包未激活";
		}

		if(!function_exists('curl_init')){
 		   $result[] = "php-curl包未激活";
		}
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

	private function backUpFiles($package)
	{
		$backupFilesDirs = array();
		$backUpdir = $this->getPackageBackUpDir($package);
		$backupFilesDirs['src'] = $this->getPackageBackUpDir($package).'src';
		$backupFilesDirs['app'] = $this->getPackageBackUpDir($package).'app';
		$backupFilesDirs['web'] = $this->getPackageBackUpDir($package).'web';

		$this->deepCopy($this->getSystemRootPath().'src',$backupFilesDirs['src']);
		$this->deepCopy($this->getSystemRootPath().'app',$backupFilesDirs['app']);
		$this->deepCopy($this->getSystemRootPath().'web',$backupFilesDirs['web']);
		return $backupFilesDirs;	
	}

	private function getFilters()
	{
		return array('app'.DIRECTORY_SEPARATOR.'data',
			   'app'.DIRECTORY_SEPARATOR.'cache',
			   'app'.DIRECTORY_SEPARATOR.'logs',
			   'app'.DIRECTORY_SEPARATOR.'logs',
			   'app'.DIRECTORY_SEPARATOR.'logs',
			   'web'.DIRECTORY_SEPARATOR.'files'
		);
	}

	private function xcopy($src,$dest)
	{
		 foreach  (scandir($src) as $file) {
		   if (!is_readable($src.DIRECTORY_SEPARATOR.$file)) continue;
		   if (is_dir($file) && ($file!='.') && ($file!='..') ) {
		       mkdir($dest . DIRECTORY_SEPARATOR . $file);
		       xcopy($src.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file);
		   } else {
		       copy($src.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file);
		   }
		}
 	}

 	private function deepCopy($src,$dest)
 	{
 		$filters = $this->getFilters();	
		if(!file_exists($src)) return ;
		mkdir($dest,0666,true);
		foreach(new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
			 \RecursiveIteratorIterator::SELF_FIRST ) as $path) {
			$relativeFile = str_replace($path->getPathname(),'',$src);
			$destFile = $dest.$relativeFile;

			if($path->isDir()){
				if($this->patternMatch($path->getPathname(),$filters)){
					conitinue;
				}
				mkdir($destFile);
			}else{
				copy($path->getPathname(),$destFile);
			}
		}
 	}

 	private function patternMatch($path,&$filters)
 	{
 		foreach ($filters as $filter) {
 			if(strrpos($path,$filter)!==false){
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
			mkdir($dir,0666,true);
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