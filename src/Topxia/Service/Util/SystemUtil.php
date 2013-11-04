<?php

namespace Topxia\Service\Util;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\MySQLDumper;

class SystemUtil
{
	public static function getDownloadPath()
	{
		return ServiceKernel::instance()->getParameter('topxia.disk.upgrade_dir');
	}

	public static function getBackUpPath()
	{
		return ServiceKernel::instance()->getParameter('topxia.disk.backup_dir');
	}	


	public static function getCachePath(){
		$realPath = ServiceKernel::instance()->getParameter('kernel.root_dir');
		$realPath .= DIRECTORY_SEPARATOR.'cache';	
		return 	$realPath;
	}

	public static function getSystemRootPath()
	{
		$realPath = ServiceKernel::instance()->getParameter('kernel.root_dir');
		return dirname($realPath).DIRECTORY_SEPARATOR;
	}

	public static function getUploadTmpPath()
	{
		$realPath = SystemUtil::getSystemRootPath().DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'tmp';
		return $realPath;
	}

	public static function backupdb()
	{
		$backUpdir = SystemUtil::getUploadTmpPath();
		$backUpdir .= DIRECTORY_SEPARATOR.uniqid(mt_rand()).'.txt';
		$dbSetting = array('exclude'=>array('session','cache'));
		$dump = new MySQLDumper(ServiceKernel::instance()->getConnection(),$dbSetting);
		return 	$dump->export($backUpdir);			
	}

}