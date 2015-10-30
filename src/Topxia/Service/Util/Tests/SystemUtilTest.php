<?php

namespace Topxoa\Service\Util\Tests;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\MySQLDumper;
use Topxia\Service\Util\SystemUtil;
use Topxia\Service\Common\BaseTestCase;

class SystemUtilTest extends BaseTestCase
{

      public function testGetDownloadPath()
      {
              $testDownloadPath = SystemUtil::getDownloadPath();
              $downloadPath = '/var/www/edusoho/app/data/upgrade';
              $this->assertEquals($downloadPath,$testDownloadPath);
      }

      public function testGetBackUpPath()
      {
              $testBackUpPath = SystemUtil::getBackUpPath();
              $backUpPath = '/var/www/edusoho/app/data/backup';
              $this->assertEquals($backUpPath,$testBackUpPath);
      }

      public function testGetCachePath()
      {
      	       $testCachePath = SystemUtil::getCachePath();
      	       $cachePath = '/var/www/edusoho/app/cache';
      	       $this->assertEquals($cachePath,$testCachePath);
      }

      public function testGetSystemRootPath()
      {
      	      $testSystemRootPath = SystemUtil::getSystemRootPath();
      	      $systemRootPath = '/var/www/edusoho/';
      	      $this->assertEquals($systemRootPath,$testSystemRootPath);
      }

      public function testGetUploadTmpPath()
      {
             $testUploadTmpPath = SystemUtil::getUploadTmpPath();
             $uploadTmpPath  = '/var/www/edusoho//web/files/tmp';
             $this->assertEquals($uploadTmpPath,$testUploadTmpPath);
      }

      public function testBackupdb()
      {
             $testBackUpdir = '/var/www/edusoho/web/files/tmp';
             $testBackUpdir .= DIRECTORY_SEPARATOR.uniqid(mt_rand()).'.txt'.'.gz';
	      $testDbSetting = array('exclude'=>array('session','cache'));
             $testDump = new MySQLDumper(ServiceKernel::instance()->getConnection(),$testDbSetting);
             $testTarget =  $testDump->export($testBackUpdir);
             $this->assertFileExists($testTarget);

             // $testTarget = SystemUtil::backupdb();
             // $this->assertFileExists($testTarget);
      }
}