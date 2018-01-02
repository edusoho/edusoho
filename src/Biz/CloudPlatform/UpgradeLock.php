<?php

namespace Biz\CloudPlatform;

use Symfony\Component\Filesystem\Filesystem;

class UpgradeLock
{
    public static function lock()
    {
        $filePath = self::_getFile();
        $initMaxTime = (int) ini_get('max_execution_time');
        $plus = ($initMaxTime <= 0) ? 120 : $initMaxTime;
        $time = time() + $plus + 30;

        file_put_contents($filePath, (string) $time);
    }

    public static function unlock()
    {
        $filePath = self::_getFile();
        $fileSystem = new Filesystem();
        @$fileSystem->remove($filePath);
    }

    private static function _getFile()
    {
        return __DIR__.'/../../../app/data/'.'upgrade.lock';
    }
}
