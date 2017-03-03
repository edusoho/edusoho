<?php

namespace Biz\CloudPlatform\Client;

interface AppClient
{
    /**
     * 获得所有应用包.
     */
    public function getApps();

    /**
     * 检查更新包.
     */
    public function checkUpgradePackages($apps, $extInfos);

    /**
     * 提交应用包升级／安装日志数据.
     */
    public function submitRunLog($log);

    /**
     * 下载应用包.
     */
    public function downloadPackage($packageId);

    /**
     * 检查是否有权限下载应用.
     */
    public function checkDownloadPackage($packageId);

    public function repairProblem($token);

    /**
     * 获得包信息.
     */
    public function getPackage($id);

    /**
     * 获取token.
     */
    public function getLoginToken();

    /**
     * 获取应用状态
     *
     * @param $code
     *
     * @return mixed
     */
    public function getAppStatusByCode($code);
}
