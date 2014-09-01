<?php
namespace Topxia\Service\CloudPlatform\Impl;

use Symfony\Component\Filesystem\Filesystem;

use Topxia\Service\CloudPlatform\AppService;
use Topxia\Service\Util\MySQLDumper;
use Topxia\Service\CloudPlatform\Client\EduSohoAppClient;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\System;

use Topxia\Service\Util\PluginUtil;

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

        $lastCheck = intval($this->getSettingService()->get('_app_last_check'));
        if (empty($lastCheck) or ((time() - $lastCheck) > 86400) ) {
            $coursePublishedCount = $this->getCourseService()->searchCourseCount(array('status'=>'published'));
            $courseUnpublishedCount = $this->getCourseService()->searchCourseCount(array('status'=>'draft'));

            $extInfos = array(
                'host' => $_SERVER['HTTP_HOST'],
                'userCount' => (string) $this->getUserService()->searchUserCount(array()),
                'coursePublishedCount' => (string) $coursePublishedCount,
                'courseUnpublishedCount' => (string) $courseUnpublishedCount,
                'courseCount' => (string) ($coursePublishedCount + $courseUnpublishedCount),
                'moneyCourseCount' => (string) $this->getCourseService()->searchCourseCount(array('status' => 'published', 'notFree' => true)),
                'lessonCount' => (string) $this->getCourseService()->searchLessonCount(array()),
                'courseMemberCount' => (string) $this->getCourseService()->searchMemberCount(array('role' => 'student')),
                'mobileLoginCount' => (string) $this->getUserService()->searchTokenCount(array('type'=>'mobile_login')),
                'teacherCount' => (string) $this->getUserService()->searchUserCount(array('roles'=>'ROLE_TEACHER')),
            );

            $this->getSettingService()->set('_app_last_check', time());
        } else {
            $extInfos = array('_t' => (string)time());
        }

        return $this->createAppClient()->checkUpgradePackages($args, $extInfos);
    }

    public function checkAppCop()
    {
        return $this->createAppClient()->checkAppCop();
    }

    public function findLogs($start, $limit)
    {
        return $this->getAppLogDao()->findLogs($start, $limit);
    }

    public function checkOwnCopyrightUser($id)
    {
        return $this->createAppClient()->checkOwnCopyrightUser($id);
    }

    public function findLogCount()
    {
        return $this->getAppLogDao()->findLogCount();
    }

    private function createPackageUpdateLog($package, $status='SUCCESS', $message='')
    {
        $result = array(
            'code'=>$package['product']['code'],
            'name'=>$package['product']['name'],
            'fromVersion'=>$package['fromVersion'],
            'toVersion'=>$package['toVersion'],
            'type'=>$package['type'],
            'status'=>$status,
            'userId'=>$this->getCurrentUser()->id,
            'ip'=>$this->getCurrentUser()->currentIp,
            'message'=>$message,
            'createdTime'=>time(),
        );
        if($package['backupDB']) {
            $result['dbBackPath'] = '';  // @todo
        }

        if($package['backupFile']) {
            $result['srcBackPath'] = ''; // @todo;
        }

        return $this->getAppLogDao()->addLog($result);
    }



    public function hasLastErrorForPackageUpdate($packageId)
    {
        $package = $this->getCenterPackageInfo($packageId);
        if (empty($package)) {
            throw $this->createServiceException("获取应用包#{$packageId}信息失败");
        }

        $log = $this->getAppLogDao()->getLastLogByCodeAndToVersion($package['product']['code'], $package['toVersion']);
        if (empty($log)) {
            return false;
        }

        return $log['status'] == 'ROLLBACK';
    }

    public function checkEnvironmentForPackageUpdate($packageId)
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

        if(!is_writeable("{$rootDirectory}/vendor")) {
            $errors[] = 'vendor目录无写权限';
        }

        if(!is_writeable("{$rootDirectory}/plugins")) {
            $errors[] = 'plugins目录无写权限';
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


        $package = $this->getCenterPackageInfo($packageId);

        $this->_submitRunLogForPackageUpdate('检查环境', $package, $errors);

        return $errors;
    }

    public function checkDependsForPackageUpdate($packageId)
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

        $this->_submitRunLogForPackageUpdate('检查依赖', $package, $errors);

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
        $this->_submitRunLogForPackageUpdate('备份数据库', $package, $errors);
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
        $this->_submitRunLogForPackageUpdate('备份文件', $package, $errors);
        return $errors; 
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

        $this->_submitRunLogForPackageUpdate('下载应用包', $package, $errors);
        return $errors;
    }

    public function checkDownloadPackageForUpdate($packageId)
    {
        $result = $this->createAppClient()->checkDownloadPackage($packageId);
        if ($result['status'] == 'ok') {
            return array();
        }
        return $result['errors'];
    }

    public function beginPackageUpdate($packageId)
    {
        $errors = array();
        $package = $packageDir = null;
        try {
            $package = $this->getCenterPackageInfo($packageId);
            if (empty($package)) {
                throw $this->createServiceException("应用包#{$packageId}不存在或网络超时，读取包信息失败");
            }
            $packageDir = $this->makePackageFileUnzipDir($package);
        } catch(\Exception $e) {
            $errors[] = $e->getMessage();
            goto last;
        }

        try {
            $this->_deleteFilesForPackageUpdate($package, $packageDir);
        } catch(\Exception $e) {
            $errors[] = "删除文件时发生了错误：{$e->getMessage()}";
            $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
            goto last;
        }

        try {
            $this->_replaceFileForPackageUpdate($package, $packageDir);
        } catch (\Exception $e) {
            $errors[] = "复制升级文件时发生了错误：{$e->getMessage()}";
            $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
            goto last;
        }

        try {
            $this->_execScriptForPackageUpdate($package, $packageDir);
        } catch (\Exception $e) {
            $errors[] = "执行升级/安装脚本时发生了错误：{$e->getMessage()}";
            $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
            goto last;
        }

        try {
            $cachePath = $this->getKernel()->getParameter('kernel.root_dir') . '/cache/' . $this->getKernel()->getEnvironment();
            $filesystem = new Filesystem();
            $filesystem->remove($cachePath);
        } catch (\Exception $e) {
            $errors[] = "应用安装升级成功，但刷新缓存失败！请检查{$cachePath}的权限";
            $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
            goto last;
        }

        if (empty($errors)) {
            $this->updateAppForPackageUpdate($package);
            $this->createPackageUpdateLog($package, 'SUCCESS');
            PluginUtil::refresh();
        }

        last:
        $this->_submitRunLogForPackageUpdate('执行升级', $package, $errors);
        return $errors;
    }

    public function repairProblem($token)
    {
        return $this->createAppClient()->repairProblem($token);
    }

    public function uninstallApp($code)
    {
        $app = $this->getAppDao()->getAppByCode($code);
        if (empty($app)) {
            throw $this->createServiceException("App {$code} is not exist.");
        }

        $uninstallScript = realpath($this->getKernel()->getParameter('kernel.root_dir') . '/../plugins/' . ucfirst($app['code']) . '/Scripts/uninstall.php');

        if (file_exists($uninstallScript)) {
            include $uninstallScript;
            $uninstaller = new \AppUninstaller($this->getKernel());
            $uninstaller->uninstall();
        }

        $this->getAppDao()->deleteApp($app['id']);

    }

    public function updateAppVersion($code,$fromVersion,$version)
    {
        $this->getAppDao()->updateAppVersion($code,$version);
        $this->getAppDao()->updateAppFromVersion($code,$fromVersion);
        
        return true;
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

    private function _submitRunLogForPackageUpdate($message, $package, $errors)
    {
        $this->createAppClient()->submitRunLog(array(
            'level' => empty($errors) ? 'info' : 'error',
            'code' => $package['product']['code'],
            'type' => $package['type'],
            'fromVersion' => empty($package['fromVersion']) ? '' : $package['fromVersion'],
            'toVersion' => empty($package['toVersion']) ? '' : $package['toVersion'],
            'message' => $message . (empty($errors) ? '成功' : '失败'),
            'data' => empty($errors) ? '' : $errors,
        ));
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
            return realpath($this->getKernel()->getParameter('kernel.root_dir') . '/../' . 'plugins');
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

    private function updateAppForPackageUpdate($package)
    {
        $newApp = array(
            'code' => $package['product']['code'],
            'name' => $package['product']['name'],
            'description' => $package['product']['description'],
            'icon' => $package['product']['icon'],
            'version' => $package['toVersion'],
            'fromVersion' => $package['fromVersion'],
            'developerId' => $package['product']['developerId'],
            'developerName' => $package['product']['developerName'],
            'updatedTime' => time(),
        );

        $app = $this->getAppDao()->getAppByCode($package['product']['code']);

        if (empty($app)) {
            $newApp['installedTime'] = time();
            return $this->getAppDao()->addApp($newApp);
        }

        return $this->getAppDao()->updateApp($app['id'], $newApp);
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

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

}