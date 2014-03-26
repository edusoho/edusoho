<?php
namespace Topxia\Service\CloudPlatform;

interface AppService 
{
    public function findApps($start, $limit);

    public function findAppCount();

    public function findAppsByCodes(array $codes);

    /**
     * 获得应用中心应用列表
     */
    public function getCenterApps();

    public function getCenterPackageInfo($id);

    public function checkAppUpgrades();

    public function findLogs($start, $limit);

    public function findLogCount();

    public function checkUpdateEnvironment();
}