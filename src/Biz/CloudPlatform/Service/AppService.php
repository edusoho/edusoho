<?php

namespace Biz\CloudPlatform\Service;

interface AppService
{
    const CORE_TYPE = 'core';
    const THEME_TYPE = 'theme';
    const PLUGIN_TYPE = 'plugin';

    public function getAppByCode($code);

    public function findApps($start, $limit);

    public function findAppCount();

    public function findAppsByCodes(array $codes);

    public function findAppsByTypes(array $types);

    public function getMainVersion();

    public function registerApp($app);

    public function updateAppVersion($id, $version);

    /**
     * 获得应用中心应用列表.
     */
    public function getCenterApps();

    public function getBinded();

    public function getCenterPackageInfo($id);

    public function checkAppUpgrades();

    public function findLogs($start, $limit);

    public function findLogCount();

    /**
     * 是否存在需要回滚的升级.
     */
    public function hasLastErrorForPackageUpdate($packageId);

    /**
     * 为安装升级应用，检查环境.
     */
    public function checkEnvironmentForPackageUpdate($packageId);

    /**
     * 为安装升级应用，检查依赖.
     */
    public function checkDependsForPackageUpdate($packageId);

    /**
     * 为安装升级应用，备份数据库.
     */
    public function backupDbForPackageUpdate($packageId);

    /**
     * 为安装升级应用，备份数据库.
     */
    public function backupFileForPackageUpdate($packageId);

    /**
     * 为安装升级应用，下载应用包.
     */
    public function downloadPackageForUpdate($packageId);

    public function checkDownloadPackageForUpdate($packageId);

    /**
     * 为安装升级应用，开始升级.
     */
    public function beginPackageUpdate($packageId, $type);

    public function repairProblem($token);

    public function uninstallApp($code);

    public function findInstallApp($code);

    public function getTokenLoginUrl($routingName, $params, $isSecure = false);

    public function getAppStatusByCode($code);
}
