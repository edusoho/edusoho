<?php

namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\UpgradeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;
use Topxia\Service\Util\MySQLDumper;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Util\FileUtil;

class UpgradeServiceImpl extends BaseService implements UpgradeService
{
	private $fileCount=0;
	private $fileSystem = null;

	private function addInstalledPackage($packageInfo)
	{
		$installedPackage = array();
		$installedPackage['ename'] = $packageInfo['ename'];
		$installedPackage['cname'] = $packageInfo['cname'];
		$installedPackage['version'] = $packageInfo['version'];
		$installedPackage['fromVersion'] = $packageInfo['fromVersion'];
		$installedPackage['installTime'] = time();
		$existPackage = $this->getInstalledPackageDao()->getInstalledPackageByEname($packageInfo['ename']);
		if(empty($existPackage)){
			return $this->getInstalledPackageDao()->addInstalledPackage($installedPackage);
		} else {
			return $this->getInstalledPackageDao()->updateInstalledPackage($existPackage['id'], 
				$installedPackage);
		}
	}

	public function commit($id,$result)
	{
		$this->getEduSohoUpgradeService()->commit($id,$result);
	}


	public function getRemoteInstallPackageInfo($id)
	{
		$package = $this->getEduSohoUpgradeService()->install($id);
		return $package;
	}

	public function getRemoteUpgradePackageInfo($id)
	{
		$package = $this->getEduSohoUpgradeService()->getPackage($id);
		return $package;
	}


	public function searchPackageCount($conditions)
	{
		return $this->getInstalledPackageDao()->searchPackageCount($conditions);
	}

	public function searchLogCount($conditions)
	{
		return $this->getUpgradeLogDao()->searchLogCount($conditions);
	}

	public function searchLogs($conditions, $start, $limit)
	{
		return $this->getUpgradeLogDao()->searchLogs($conditions, $start, $limit);
	}

	public function searchPackages($conditions, $start, $limit)
	{
		return $this->getInstalledPackageDao()->searchPackages($conditions, $start, $limit);
	}

	public function check()
	{
		$packages = $this->getInstalledPackageDao()->findInstalledPackages();
		if(!$this->checkMainVersion($packages)){
			$packages =$this->addMainVersionAndReloadPackages();
		}
		return $this->getEduSohoUpgradeService()->check($packages);
	}

	public function repairProblem($token)
	{
		return $this->getEduSohoUpgradeService()->repairProblem($token);
	}

	public function hasLastError($id)
	{
		$package = $this->getEduSohoUpgradeService()->getPackage($id);
		if(empty($package)) throw $this->createServiceException("不存在{$id}");
		$log = $this->getUpgradeLogDao()->getUpdateLogByEnameAndVersion($package['ename'], $package['version']);
		if('ROLLBACK' == $log['status']){
			return true;
		}
		return false;
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

		if (!FileUtil::is_writable($this->getDownloadPath())){
			$result[] = "下载目录({$this->getDownloadPath()})无写权限<br>";
		}
		if (!FileUtil::is_writable($this->getBackUpPath())){
			$result[] = "备份目录{$this->getBackUpPath()})无写权限<br>";
		}
		if(!FileUtil::is_writable($this->getSystemRootPath().'app')){
			$result[] = 'app目录无写权限<br>';
		}
		if(!FileUtil::is_writable($this->getSystemRootPath().'src')){
			$result[] = 'src目录无写权限<br>';
		}
		if(!FileUtil::is_writable($this->getSystemRootPath().'web')){
			$result[] = 'web目录无写权限<br>';
		}
		if(!FileUtil::is_writable($this->getSystemRootPath().'app'.DIRECTORY_SEPARATOR.'cache')){
			$result[] = 'app/cache目录无写权限<br>';
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
				$result[]= " 没有安装 {$depend['o']} {$depend['v']} 版本的{$depend['cname']} \n <br>";
				continue;
			}
			if(!version_compare($installed['version'],$depend['v'],$depend['o'])){
				$result[]=  " 该安装包依赖 {$depend['o']} {$depend['v']} 版本的{$depend['cname']}，而当前版本为:{$installed['version']} \n <br>";
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
			if(isset($package['backupDB']) && $package['backupDB']){
				$this->backUpDb($package);
			}
			if(isset($package['backupFile']) && $package['backupFile']){
				$this->backUpFiles($package);
			}

		}catch(\Exception $e){
			$result[] = $e->getMessage().'<br>';
			return $result;
		}	
		return $result;	
	}

	public function beginUpgrade($id)
	{
		$result = array();
		$touched = false;
		try{
			$package = $this->getEduSohoUpgradeService()->getPackage($id);
			$deletes = $this->getExtractPath($package).DIRECTORY_SEPARATOR.'delete';
			$this->deleteFiles($deletes);
			$source = $this->getExtractPath($package).DIRECTORY_SEPARATOR.'source'.DIRECTORY_SEPARATOR;
			$count = FileUtil::deepCopy($source,$this->getSystemRootPath());
			$this->fileCount += $count;
			$upgradeFile = $this->getExtractPath($package).DIRECTORY_SEPARATOR.'Upgrade.php';
			if($this->getFileSystem()->exists($upgradeFile)){
				include_once($upgradeFile);
				$upgrade = new \EduSohoUpgrade($this->getKernel());
				if(method_exists($upgrade, 'update')){
					$touched = true;
					$upgrade->update();
				}
			}
		}catch(\Exception $e){
			$result[]= "当前总共升级了{$this->fileCount}个文件，升级文件无法继续进行，原因: {$e->getMessage()}";
			if($this->fileCount>0) {
				$touched = true;
			}
			if($touched){
				$this->createUpgradeLog($package,'ROLLBACK',$e->getMessage());
				$this->getLogService()->info('upgrade', 'upgrade', "更新失败，需要恢复备份！ 更新包-{$package['cname']}({$package['ename']})");
			}else{
				$this->createUpgradeLog($package,'ERROR',$e->getMessage());
				$this->getLogService()->info('upgrade', 'upgrade', "更新失败，更新包-{$package['cname']}({$package['ename']})");
			}
			return $result;
		}
		$this->addInstalledPackage($package);

		$this->createUpgradeLog($package);
		$this->getLogService()->info('upgrade', 'upgrade', "成功更新包-{$package['cname']}({$package['ename']})");

		return $result;
	}

	private function makeException()
	{
		throw new \Exception('权限的原因无法写文件，请检查文件访问权限以及文件的用户和用户组！');		
	}



	public function refreshCache(){
		$path = $this->getCachePath();
		FileUtil::emptyDir($path);
	}



	private function createUpgradeLog($package,$status='SUCCESS',$reason=null)
	{
		$installed = $this->getInstalledPackageDao()->getInstalledPackageByEname($package['ename']);
		;
 		$result = array('remoteId'=>$package['id'],
 			'installedId'=>$installed['id'],
 			'ename'=>$package['ename'],
 			'cname'=>$package['cname'],
 			'fromv'=>$package['fromVersion'],
 			'tov'=>$package['version'],
 			'type'=>$package['type'],
 			'status'=>$status,
 			'logtime'=>time(),
 			'uid'=>$this->getCurrentUser()->id,
 			'ip'=>$this->getCurrentUser()->currentIp,
 			'reason'=>$reason
		);
		if($package['backupDB'])
		 	$result['dbBackPath']=$this->getBackupFilePath($package).'.gz';
		if($package['backupFile'])
 			$result['srcBackPath']=$this->getPackageBackUpDir($package);
		return $this->getUpgradeLogDao()->addLog($result);
	}

	private function deleteFiles($deletes){
		if(!$this->getFileSystem()->exists($deletes)) return ;
		$fh = fopen($deletes,'r');
		$this->fileCount = 0;
		while ($line = fgets($fh)) {
			$rawPath =  rtrim($this->getSystemRootPath().DIRECTORY_SEPARATOR.$line);
			$this->getFileSystem()->remove($rawPath);
  			$this->fileCount ++;
		}
		fclose($fh);		
	}

	private function backUpFiles($package)
	{
		$backupFilesDirs = array();
		$backUpdir = $this->getPackageBackUpDir($package);
		$backupFilesDirs['src'] = $this->getPackageBackUpDir($package).'src';
		$backupFilesDirs['app'] = $this->getPackageBackUpDir($package).'app';
		$backupFilesDirs['web'] = $this->getPackageBackUpDir($package).'web';
		$count = FileUtil::deepCopy($this->getAbsoluteDir('src'),$backupFilesDirs['src']);
		$this->fileCount += $count;
		$count = FileUtil::deepCopy($this->getAbsoluteDir('app'),$backupFilesDirs['app'],array($this,'patternMatch'));
		$this->fileCount += $count;
		$count = FileUtil::deepCopy($this->getAbsoluteDir('web'),$backupFilesDirs['web']);
		$this->fileCount += $count;

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

	private function getAbsoluteDir($mainPath)
	{
		return $this->getSystemRootPath().DIRECTORY_SEPARATOR.'src';
	}


 	public function patternMatch($path)
 	{
 		$filters = $this->getFilters();
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
		$dump = new MySQLDumper($this->getKernel()->getConnection(),$dbSetting);
		$backUpdir = $this->getPackageBackUpDir($package);
		FileUtil::emptyDir($backUpdir);
		return 	$dump->export($this->getBackupFilePath($package));	
	}

	private function getBackupFilename($package){
		$backUpdir = $this->getPackageBackUpDir($package);
		return basename($backUpdir);
	}

	private function getBackupFilePath($package){
		$backUpdir = $this->getPackageBackUpDir($package);
		$backUpdir .= basename($backUpdir);
		return $backUpdir;
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
		if(!$this->getFileSystem()->exists($dir)){
			$this->getFileSystem()->mkdir($dir,0777);
		}
		return $dir;

	}


	private function extractFile($path)
	{

		$dir = $this->getDownloadPath();
		$extractPath = $dir.DIRECTORY_SEPARATOR.basename($path, ".zip");

		$this->getFileSystem()->remove($extractPath);
		$zip = new \ZipArchive;
		if ($zip->open($path) === TRUE) {
    		$zip->extractTo($dir);
    		$zip->close();
		} else {
    		throw new \Exception('无法解压缩安装包！');
		}
		return $extractPath;
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

	private function getCachePath(){
		$realPath = $this->getKernel()->getParameter('kernel.root_dir');
		$realPath .= DIRECTORY_SEPARATOR.'cache';	
		return 	$realPath;
	}

	private function getSystemRootPath()
	{
		$realPath = $this->getKernel()->getParameter('kernel.root_dir');
		return dirname($realPath).DIRECTORY_SEPARATOR;
	}

    private function getInstalledPackageDao ()
    {
        return $this->createDao('Upgrade.InstalledPackageDao');
    }	

    private function getUpgradeLogDao ()
    {
        return $this->createDao('Upgrade.UpgradeLogDao');
    }	

    private function getEduSohoUpgradeService ()
    {
        return $this->createService('Upgrade.EduSohoUpgradeService');
    }	

    private function getFileSystem()
    {
    	if($this->fileSystem==null){
    		$this->fileSystem = new FileSystem();
    	}
    	return $this->fileSystem;
    }
    protected function getLogService()
    {
        return $this->createService('System.LogService');        
    }  
}