<?php
namespace Topxia\Service\CloudPlatform;

interface AppService 
{
    public function findApps($start, $limit);

    public function findAppCount();

    public function getCenterApps();

    public function checkAppUpgrades();

    public function findLogs($start, $limit);

    public function findLogCount();
}