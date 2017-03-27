<?php

namespace Topxia\Service\Util\Tests;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\MySQLDumper;
use Topxia\Service\Util\SystemUtil;
use Topxia\Service\Common\BaseTestCase;

class SystemUtilTest extends BaseTestCase
{

      public function testGetDownloadPath()
      {
              $testDownloadPath = SystemUtil::getDownloadPath();
              $downloadPath = SystemUtil::getSystemRootPath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'upgrade';
              $this->assertEquals($downloadPath,$testDownloadPath);
      }

      public function testGetBackUpPath()
      {
              $testBackUpPath = SystemUtil::getBackUpPath();
              $backUpPath = SystemUtil::getSystemRootPath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'backup';
              $this->assertEquals($backUpPath,$testBackUpPath);
      }

      public function testGetCachePath()
      {
      	       $testCachePath = SystemUtil::getCachePath();
              $cachePath = SystemUtil::getSystemRootPath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache';
      	       $this->assertEquals($cachePath,$testCachePath);
      }

      public function testGetSystemRootPath()
      {
      	      $testSystemRootPath = SystemUtil::getSystemRootPath();
             $systemRootPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
      	      $this->assertEquals($systemRootPath,$testSystemRootPath);
      }

      public function testGetUploadTmpPath()
      {
             $testUploadTmpPath = SystemUtil::getUploadTmpPath();
             $uploadTmpPath = SystemUtil::getSystemRootPath().DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'tmp';
             $this->assertEquals($uploadTmpPath,$testUploadTmpPath);
      }

      public function testBackupdb()
      {
             /*$testTarget = SystemUtil::backupdb();
             $this->assertFileExists($testTarget);*/
      }
}