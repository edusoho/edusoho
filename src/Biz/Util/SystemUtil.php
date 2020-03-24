<?php

namespace Biz\Util;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\RandMachine;

class SystemUtil
{
    private static $mockedDump;

    public static function getDownloadPath()
    {
        return ServiceKernel::instance()->getParameter('topxia.disk.upgrade_dir');
    }

    public static function getBackUpPath()
    {
        return ServiceKernel::instance()->getParameter('topxia.disk.backup_dir');
    }

    public static function getCachePath()
    {
        $realPath = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $realPath .= DIRECTORY_SEPARATOR.'cache';

        return $realPath;
    }

    public static function getSystemRootPath()
    {
        $realPath = ServiceKernel::instance()->getParameter('kernel.root_dir');

        return dirname($realPath);
    }

    public static function getUploadTmpPath()
    {
        $realPath = self::getSystemRootPath().DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'tmp';

        return $realPath;
    }

    public static function getPrivateTmpPath()
    {
        return ServiceKernel::instance()->getParameter('topxia.upload.private_directory').DIRECTORY_SEPARATOR.'tmp';
    }

    public static function backupdb()
    {
        $backUpdir = self::getUploadTmpPath();
        $backUpdir .= DIRECTORY_SEPARATOR.RandMachine::uniqidWithMtRand().'.txt';
        $dbSetting = array('exclude' => array('session', 'cache'));

        if (empty(self::$mockedDump)) {
            $dump = new MySQLDumper(ServiceKernel::instance()->getConnection(), $dbSetting);
        } else {
            $dump = self::$mockedDump;
        }

        if (empty(self::$mockedDump)) {
            $dump = new MySQLDumper(ServiceKernel::instance()->getConnection(), $dbSetting);

            return $dump->export($backUpdir);
        } else {
            return self::$mockedDump->export($backUpdir);
        }
    }
}
