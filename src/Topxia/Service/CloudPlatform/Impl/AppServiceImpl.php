<?php
namespace Topxia\Service\CloudPlatform\Impl;

use Topxia\Service\CloudPlatform\AppService;
use Topxia\Service\CloudPlatform\Client\EduSohoAppClient;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;

class AppServiceImpl extends BaseService implements AppService
{
    const MAX_APP_COUNT = 100;

    private $client;

    public function findApps($start, $limit)
    {
        return $this->getAppDao()->findApps($start, $limit);
    }

    public function findAppCount()
    {
        return $this->getAppDao()->findAppCount();
    }

    public function findAppsByCodes(array $codes)
    {
        $apps = $this->getAppDao()->findAppsByCodes($codes);
        return ArrayToolkit::index($apps, 'code');
    }

    public function getCenterApps()
    {
        return $this->createAppClient()->getApps();
    }

    public function getCenterPackageInfo($id)
    {
        return $this->createAppClient()->getPackage($id);
    }

    public function checkAppUpgrades()
    {
        $mainApp = $this->getAppDao()->getAppByCode('MAIN');
        if (empty($mainApp)) {
            $this->addEduSohoMainApp();
        }
        $apps = $this->findApps(0, self::MAX_APP_COUNT);

        $args = array();
        foreach ($apps as $app) {
            $args[$app['code']] = $app['version'];
        }

        return $this->createAppClient()->checkUpgradePackages($args);
    }

    public function findLogs($start, $limit)
    {
        return $this->getAppLogDao()->findLogs($start, $limit);
    }

    public function findLogCount()
    {
        return $this->getAppLogDao()->findLogCount();
    }

    public function checkUpdateEnvironment()
    {
        $errors = array();

        if(!class_exists('ZipArchive')) {
           $errors[] = "php_zip扩展未激活";
        }

        if(!function_exists('curl_init')) {
           $errors[] = "php_curl扩展未激活";
        }

        if (!is_writeable($this->getDownloadDirectory())) {
            $errors[] = "下载目录({$this->getDownloadDirectory()})无写权限";
        }

        if (!is_writeable($this->getBackUpDirectory())) {
            $errors[] = "备份目录{$this->getDownloadDirectory()})无写权限";
        }

        $rootDirectory = $this->getSystemRootDirectory();

        if(!is_writeable("{$rootDirectory}/app")) {
            $errors[] = 'app目录无写权限';
        }

        if(!is_writeable("{$rootDirectory}/src")) {
            $errors[] = 'src目录无写权限';
        }

        if(!is_writeable("{$rootDirectory}/web")) {
            $errors[] = 'web目录无写权限';
        }

        if(!is_writeable("{$rootDirectory}/app/cache")) {
            $errors[] = 'app/cache目录无写权限';
        }

        if(!is_writeable("{$rootDirectory}/app/data")) {
            $errors[] = 'app/data目录无写权限';
        }

        if(!is_writeable("{$rootDirectory}/app/config")) {
            $errors[] = 'app/config目录无写权限';
        }

        if(!is_writeable("{$rootDirectory}/app/config/config.yml")) {
            $errors[] = 'app/config/config.yml文件无写权限';
        }

        return $errors;
    }

    private function getSystemRootDirectory()
    {
        return dirname($this->getKernel()->getParameter('kernel.root_dir'));
    }

    private function getDownloadDirectory()
    {
        return $this->getKernel()->getParameter('topxia.disk.update_dir');
    }

    private function getBackUpDirectory()
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
        return  $realPath;
    }





    private function hasEduSohoMainApp($apps)
    {
        foreach ($apps as $app) {
            if($app['code'] === 'MAIN') {
                return true;
            }
        }
        return false;
    }

    private function addEduSohoMainApp()
    {
        $app = array(
            'code' => 'MAIN',
            'name' => 'EduSoho主系统',
            'description' => 'EduSoho主系统',
            'icon' => '',
            'version' => System::VERSION,
            'fromVersion' => '0.0.0',
            'developerId' => 1,
            'developerName' => 'EduSoho官方',
            'installedTime' => time(),
            'updatedTime' => time(),
        );
        $this->getAppDao()->addApp($app);
    }


    private function getAppDao ()
    {
        return $this->createDao('CloudPlatform.CloudAppDao');
    }

    private function getAppLogDao ()
    {
        return $this->createDao('CloudPlatform.CloudAppLogDao');
    }

    private function createAppClient()
    {
        if (!isset($this->client)) {
            $cloud = $this->getSettingService()->get('storage', array());
            $developer = $this->getSettingService()->get('developer', array());

            $options = array(
                'accessKey' => empty($cloud['cloud_access_key']) ? null : $cloud['cloud_access_key'],
                'secretKey' => empty($cloud['cloud_secret_key']) ? null : $cloud['cloud_secret_key'],
                'apiUrl' => empty($developer['app_api_url']) ? null : $developer['app_api_url'],
                'debug' => empty($developer['debug']) ? false : true,
            );

            $this->client = new EduSohoAppClient($options);
        }
        return $this->client;
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

}