<?php
namespace Topxia\Service\CloudPlatform\Impl;

use Symfony\Component\Filesystem\Filesystem;

use Topxia\Service\CloudPlatform\AppService;
use Topxia\Service\Util\MySQLDumper;
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

    public function checkPackageUpdateEnvironment()
    {
        $errors = array();

        if(!class_exists('ZipArchive')) {
           $errors[] = "php_zip扩展未激活";
        }

        if(!function_exists('curl_init')) {
           $errors[] = "php_curl扩展未激活";
        }

        $filesystem = new Filesystem();

        $downloadDirectory = $this->getDownloadDirectory();
        if ($filesystem->exists($downloadDirectory)) {
            if (!is_writeable($downloadDirectory)) {
                $errors[] = "下载目录({$downloadDirectory})无写权限";
            }
        } else {
            try {
                $filesystem->mkdir($downloadDirectory);
            } catch (\Exception $e) {
                $errors[] = "下载目录({$downloadDirectory})创建失败";
            }
        }

        $backupdDirectory = $this->getBackUpDirectory();
        if ($filesystem->exists($backupdDirectory)) {
            if (!is_writeable($backupdDirectory)) {
                $errors[] = "备份({$backupdDirectory})无写权限";
            }
        } else {
            try {
                $filesystem->mkdir($backupdDirectory);
            } catch (\Exception $e) {
                $errors[] = "备份({$backupdDirectory})创建失败";
            }
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

    public function checkPackageUpdateDepends($packageId)
    {
        $errors = array();

        try {
            $package = $this->getCenterPackageInfo($packageId);
            if (!version_compare(System::VERSION, $package['edusohoMinVersion'], '>=')) {
                $errors[] = "EduSoho版本需大于等于{$package['edusohoMinVersion']}，您的版本为" . System::VERSION . '，请先升级EduSoho';
            }
        } catch(\Exception $e) {
            $errors[] = $e->getMessage();
        }

        // @todo 依赖包检测
        
        return $errors;
    }

    public function backupDbForPackageUpdate($packageId)
    {

        $errors = array();
        try {
            $filesystem = new Filesystem();

            $package = $this->getCenterPackageInfo($packageId);
            if (empty($package)) {
                $errors[] = "获取应用包#{$packageId}信息失败";
                goto last;
            }
            if (empty($package['backupDB'])) {
                goto last;
            }

            $dumper = new MySQLDumper($this->getKernel()->getConnection(), array(
                'exclude'=>array('session','cache')
            ));

            $targetBaseDir = "{$this->getBackUpDirectory()}/{$package['id']}_{$package['type']}_{$package['fromVersion']}_to_{$package['toVersion']}_db";
            $dumper->export($targetBaseDir);

        } catch(\Exception $e) {
            $errors[] = $e->getMessage();
        }
        last:
        return $errors; 

    }

    public function backupFileForPackageUpdate($packageId)
    {
        $errors = array();
        try {
            $filesystem = new Filesystem();

            $package = $this->getCenterPackageInfo($packageId);


            if (empty($package)) {
                $errors[] = "获取应用包#{$packageId}信息失败";
                goto last;
            }
            if (empty($package['backupFile'])) {
                goto last;
            }

            $targetBaseDir = "{$this->getBackUpDirectory()}/{$package['id']}_{$package['type']}_{$package['fromVersion']}_to_{$package['toVersion']}";

            if (!$filesystem->exists($targetBaseDir)) {
                $filesystem->mkdir($targetBaseDir);
            }

            $originDirs = array(
                'app/Resources',
                'app/config',
                'src',
                'web/assets',
                'web/bundles',
                'web/themes',
            );
            foreach ($originDirs as $originDir) {
                $originFullDir = $this->getSystemRootDirectory() . '/' . $originDir;
                if (!$filesystem->exists($originFullDir)) {
                    continue;
                }
                $filesystem->mirror($originFullDir, $targetBaseDir . '/' . $originDir, null, array(
                    'override' => true,
                    'copy_on_windows' => true
                ));
            }

            $originFiles = array(
                'app/AppCache.php',
                'app/AppKernel.php',
                'app/autoload.php',
                'app/bootstrap.php.cache',
                'app/console',
                'web/app.php',
            );
            foreach ($originFiles as $originFile) {
                $originFullFile = $this->getSystemRootDirectory() . '/' . $originFile;
                if (!$filesystem->exists($originFullFile)) {
                    continue;
                }
                $filesystem->copy($originFullFile, $targetBaseDir . '/' . $originFile, true);
            }

        } catch(\Exception $e) {
            $errors[] = $e->getMessage();
        }
        last:
        return $errors; 
    }

    public function hasLastRollbackErrorForPackageUpdate($packageId)
    {
        $package = $this->getCenterPackageInfo($packageId);
        if (empty($package)) {
            throw $this->createServiceException("应用包#{$packageId}不存在或网络超时，读取包信息失败");
        }

        $log = $this->getUpgradeLogDao()->getUpdateLogByEnameAndVersion($package['ename'], $package['version']);
        if('ROLLBACK' == $log['status']){
            return true;
        }
        return false;
    }

    public function downloadPackageForUpdate($packageId)
    {
        $errors = array();
        try {
            $package = $this->getCenterPackageInfo($packageId);
            if (empty($package)) {
                throw $this->createServiceException("应用包#{$packageId}不存在或网络超时，读取包信息失败");
            }

            $filepath = $this->createAppClient()->downloadPackage($packageId);

            $this->unzipPackageFile($filepath, $this->makePackageFileUnzipDir($package));

        } catch(\Exception $e) {
            $errors[] = $e->getMessage();
        }
        return $errors;
    }

    public function beginPackageUpdate($packageId)
    {
        $errors = array();
        try {
            $package = $this->getCenterPackageInfo($packageId);
            if (empty($package)) {
                throw $this->createServiceException("应用包#{$packageId}不存在或网络超时，读取包信息失败");
            }

            $packageDir = $this->makePackageFileUnzipDir($package);
            $this->_deleteFilesForPackageUpdate($package, $packageDir);
            $this->_replaceFileForPackageUpdate($package, $packageDir);
            $this->_execScriptForPackageUpdate($package, $packageDir);

        } catch(\Exception $e) {
            $errors[] = $e->getMessage();
        }
        return $errors;
    }

    private function _replaceFileForPackageUpdate($package, $packageDir)
    {
        $filesystem = new Filesystem();
        $filesystem->mirror("{$packageDir}/source",  $this->getPackageRootDirectory($package) , null, array(
            'override' => true,
            'copy_on_windows' => true
        ));
    }

    private function _execScriptForPackageUpdate($package, $packageDir)
    {
        if (!file_exists($packageDir . '/Upgrade.php')) {
            return ;
        }

        include_once($packageDir . '/Upgrade.php');
        $upgrade = new \EduSohoUpgrade($this->getKernel());
        if(method_exists($upgrade, 'update')){
            $upgrade->update();
        }
    }

    private function _deleteFilesForPackageUpdate($package, $packageDir)
    {
        if (!file_exists($packageDir . '/delete')) {
            return ;
        }

        $filesystem = new Filesystem();
        $fh = fopen($packageDir . '/delete', 'r');
        while ($filepath = fgets($fh)) {
            $fullpath = $this->getPackageRootDirectory($package). '/' . trim($filepath);
            if (file_exists($fullpath)) {
                $filesystem->remove($fullpath);
            }
        }
        fclose($fh);
    }

    private function unzipPackageFile($filepath, $unzipDir)
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($unzipDir)) {
            $filesystem->remove($unzipDir);
        }

        $tmpUnzipDir = $unzipDir . '_tmp';
        if ($filesystem->exists($tmpUnzipDir)) {
            $filesystem->remove($tmpUnzipDir);
        }
        $filesystem->mkdir($tmpUnzipDir);

        $zip = new \ZipArchive;
        if ($zip->open($filepath) === TRUE) {
            $tmpUnzipFullDir = $tmpUnzipDir . '/' . $zip->getNameIndex(0);
            $zip->extractTo($tmpUnzipDir);
            $zip->close();
            $filesystem->rename($tmpUnzipFullDir, $unzipDir);
            $filesystem->remove($tmpUnzipDir);
        } else {
            throw new \Exception('无法解压缩安装包！');
        }
    }

    private function getPackageRootDirectory($package) 
    {
        if ($package['product']['code'] == 'MAIN') {
            return $this->getSystemRootDirectory();
        } else {
            return $this->getKernel()->getParameter('kernel.root_dir') . '/' . 'bundles';
        }
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


    private function makePackageFileUnzipDir($package)
    {
        return $this->getDownloadDirectory(). '/' . $package['fileName'];
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