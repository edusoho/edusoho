<?php
namespace Topxia\Service\CloudPlatform\Impl;


use Permission\Service\Role\Impl\RoleServiceImpl;
use Topxia\Service\CloudPlatform\UpgradeLock;
use Topxia\System;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\PluginUtil;
use Topxia\Service\Util\MySQLDumper;
use Topxia\Service\Common\BaseService;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\AppService;
use Topxia\Service\CloudPlatform\Client\EduSohoAppClient;
use Topxia\Service\CloudPlatform\Dao\Impl\CloudAppDaoImpl;

class AppServiceImpl extends BaseService implements AppService
{
    const MAX_APP_COUNT = 100;

    private $client;

    public function getAppByCode($code)
    {
        return $this->getAppDao()->getAppByCode($code);
    }

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

    public function getBinded()
    {
        return $this->createAppClient()->getBinded();
    }

    public function getCenterPackageInfo($id)
    {
        return $this->createAppClient()->getPackage($id);
    }

    public function getMainVersion()
    {
        $app = $this->getAppDao()->getAppByCode('MAIN');

        return $app['version'];
    }

    public function registerApp($app)
    {
        if (!ArrayToolkit::requireds($app, array('code', 'name', 'version'))) {
            throw $this->createServiceException($this->getKernel()->trans('参数缺失,注册APP失败!'));
        }

        $app = ArrayToolkit::parts($app, array('code', 'name', 'description', 'version', 'type'));

        $app['fromVersion']   = $app['version'];
        $app['description']   = empty($app['description']) ? '' : $app['description'];
        $app['icon']          = empty($app['icon']) ? '' : $app['icon'];
        $app['developerId']   = 0;
        $app['developerName'] = empty($app['author']) ? '未知' : $app['author'];
        $app['installedTime'] = time();
        $app['updatedTime']   = time();

        $exist = $this->getAppDao()->getAppByCode($app['code']);

        if ($exist) {
            return $this->getAppDao()->updateApp($exist['id'], $app);
        }

        return $this->getAppDao()->addApp($app);
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

        if (empty($lastCheck) || ((time() - $lastCheck) > 86400)) {
            $coursePublishedCount   = $this->getCourseService()->searchCourseCount(array('status' => 'published'));
            $courseUnpublishedCount = $this->getCourseService()->searchCourseCount(array('status' => 'draft'));

            $extInfos = array(
                'host'                   => $_SERVER['HTTP_HOST'],
                'userCount'              => (string)$this->getUserService()->searchUserCount(array()),
                'coursePublishedCount'   => (string)$coursePublishedCount,
                'courseUnpublishedCount' => (string)$courseUnpublishedCount,
                'courseCount'            => (string)($coursePublishedCount + $courseUnpublishedCount),
                'moneyCourseCount'       => (string)$this->getCourseService()->searchCourseCount(array('status' => 'published', 'originPrice_GT' => '0.00')),
                'lessonCount'            => (string)$this->getCourseService()->searchLessonCount(array()),
                'courseMemberCount'      => (string)$this->getCourseService()->searchMemberCount(array('role' => 'student')),
                'mobileLoginCount'       => (string)$this->getUserService()->searchTokenCount(array('type' => 'mobile_login')),
                'teacherCount'           => (string)$this->getUserService()->searchUserCount(array('roles' => 'ROLE_TEACHER'))
            );

            $this->getSettingService()->set('_app_last_check', time());
        } else {
            $extInfos = array('_t' => (string)time());
        }

        return $this->createAppClient()->checkUpgradePackages($args, $extInfos);
    }

    public function getMessages()
    {
        return $this->createAppClient()->getMessages();
    }

    public function findLogs($start, $limit)
    {
        return $this->getAppLogDao()->findLogs($start, $limit);
    }

    public function findLogCount()
    {
        return $this->getAppLogDao()->findLogCount();
    }

    protected function createPackageUpdateLog($package, $status = 'SUCCESS', $message = '')
    {
        $result = array(
            'code'        => $package['product']['code'],
            'name'        => $package['product']['name'],
            'fromVersion' => $package['fromVersion'],
            'toVersion'   => $package['toVersion'],
            'type'        => $package['type'],
            'status'      => $status,
            'userId'      => $this->getCurrentUser()->id,
            'ip'          => $this->getCurrentUser()->currentIp,
            'message'     => $message,
            'createdTime' => time()
        );

        if ($package['backupDB']) {
            $result['dbBackupPath'] = ''; // @todo
        }

        if ($package['backupFile']) {
            $result['sourceBackupPath'] = ''; // @todo;
        }

        return $this->getAppLogDao()->addLog($result);
    }

    public function hasLastErrorForPackageUpdate($packageId)
    {
        $package = $this->getCenterPackageInfo($packageId);

        if (empty($package)) {
            throw $this->createServiceException($this->getKernel()->trans('获取应用包#%packageId%信息失败', array('%packageId%' => $packageId)));
        }

        $log = $this->getAppLogDao()->getLastLogByCodeAndToVersion($package['product']['code'], $package['toVersion']);

        if (empty($log)) {
            return false;
        }

        return $log['status'] == 'ROLLBACK';
    }

    public function checkEnvironmentForPackageUpdate($packageId)
    {
        UpgradeLock::lock();

        $errors = array();

        if (!class_exists('ZipArchive')) {
            $errors[] = $this->getKernel()->trans('php_zip扩展未激活');
        }

        if (!function_exists('curl_init')) {
            $errors[] = $this->getKernel()->trans('php_curl扩展未激活');
        }

        $filesystem = new Filesystem();

        $downloadDirectory = $this->getDownloadDirectory();

        if ($filesystem->exists($downloadDirectory)) {
            if (!is_writeable($downloadDirectory)) {
                $errors[] = $this->getKernel()->trans('下载目录(%downloadDirectory%)无写权限', array('%downloadDirectory%' => $downloadDirectory));
            }
        } else {
            try {
                $filesystem->mkdir($downloadDirectory);
            } catch (\Exception $e) {
                $errors[] = $this->getKernel()->trans('下载目录(%downloadDirectory%)创建失败', array('%downloadDirectory%' => $downloadDirectory));
            }
        }

        $backupdDirectory = $this->getBackUpDirectory();

        if ($filesystem->exists($backupdDirectory)) {
            if (!is_writeable($backupdDirectory)) {
                $errors[] = $this->getKernel()->trans('备份(%backupdDirectory%)无写权限', array('%backupdDirectory%' => $backupdDirectory));
            }
        } else {
            try {
                $filesystem->mkdir($backupdDirectory);
            } catch (\Exception $e) {
                $errors[] = $this->getKernel()->trans('备份(%backupdDirectory%)创建失败', array('%backupdDirectory%' => $backupdDirectory));
            }
        }

        $rootDirectory = $this->getSystemRootDirectory();

        if (!is_writeable("{$rootDirectory}/app")) {
            $errors[] = $this->getKernel()->trans('app目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/src")) {
            $errors[] = $this->getKernel()->trans('src目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/vendor2")) {
            $errors[] = $this->getKernel()->trans('vendor2目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/plugins")) {
            $errors[] = $this->getKernel()->trans('plugins目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/web")) {
            $errors[] = $this->getKernel()->trans('web目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/app/cache")) {
            $errors[] = $this->getKernel()->trans('app/cache目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/app/data")) {
            $errors[] = $this->getKernel()->trans('app/data目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/app/config")) {
            $errors[] = $this->getKernel()->trans('app/config目录无写权限');
        }

        if (!is_writeable("{$rootDirectory}/app/config/config.yml")) {
            $errors[] = $this->getKernel()->trans('app/config/config.yml文件无写权限');
        }

        $package = $this->getCenterPackageInfo($packageId);

        $this->_submitRunLogForPackageUpdate($this->getKernel()->trans('检查环境'), $package, $errors);

        if(!empty($errors)){
            UpgradeLock::unlock();
        }

        return $errors;
    }

    public function checkDependsForPackageUpdate($packageId)
    {
        UpgradeLock::lock();
        $errors = array();

        try {
            $package = $this->getCenterPackageInfo($packageId);
            // $errors  = $this->checkPluginDepend($package);

            if (!version_compare(System::VERSION, $package['edusohoMinVersion'], '>=')) {
                $errors[] = $this->trans('EduSoho版本需大于等于%packageEdusohoMinVersion%，您的版本为%systemVersion%，请先升级EduSoho', array('%packageEdusohoMinVersion%' => $package['edusohoMinVersion'], '%systemVersion%' => System::VERSION));
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        $this->_submitRunLogForPackageUpdate($this->getKernel()->trans('检查依赖'), $package, $errors);

        // @todo 依赖包检测
        if(!empty($errors)){
            UpgradeLock::unlock();
        }
        return $errors;
    }

    /**
     *
     * 如果当前升级的是Edusoho则检测已经安装的插件对Edusoho版本以来的检测
     * @param  $package
     * @return array
     */
    protected function checkPluginDepend($package)
    {
        if ($package['product']['code'] != 'MAIN') {
            return array();
        }
        $count         = $this->getAppDao()->findAppCount();
        $apps          = $this->getAppDao()->findApps(0, $count);
        $apps          = ArrayToolkit::index($apps, 'code');
        $systemVersion = $apps['MAIN']['version'];
        unset($apps['MAIN']);

        $errors = array_map(function ($app) use ($systemVersion) {
            if ($app['edusohoMaxVersion'] != 'up' && version_compare($app['edusohoMaxVersion'], $systemVersion, "<=")) {
                return "{$app['name']}支持的最大版本为{$app['edusohoMaxVersion']},您需要升级该插件";
            }
        }, $apps);
        return array_filter($errors);
    }

    public function backupDbForPackageUpdate($packageId)
    {
        UpgradeLock::lock();
        $errors = array();
        try {
            $filesystem = new Filesystem();

            $package = $this->getCenterPackageInfo($packageId);

            if (empty($package)) {
                $errors[] = $this->getKernel()->trans('获取应用包#%packageId%信息失败', array('%packageId%' => $packageId));
                goto last;
            }

            if (empty($package['backupDB'])) {
                goto last;
            }

            $dumper = new MySQLDumper($this->getKernel()->getConnection(), array(
                'exclude' => array('session', 'cache')
            ));

            $targetBaseDir = "{$this->getBackUpDirectory()}/{$package['id']}_{$package['type']}_{$package['fromVersion']}_to_{$package['toVersion']}_db";
            $dumper->export($targetBaseDir);
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        last:
        if(!empty($errors)){
            UpgradeLock::unlock();
        }
        $this->_submitRunLogForPackageUpdate($this->getKernel()->trans('备份数据库'), $package, $errors);
        return $errors;
    }

    public function backupFileForPackageUpdate($packageId)
    {
        UpgradeLock::lock();
        $errors = array();
        try {
            $filesystem = new Filesystem();

            $package = $this->getCenterPackageInfo($packageId);

            if (empty($package)) {
                $errors[] = $this->getKernel()->trans('获取应用包#%packageId%信息失败', array('%packageId%' => $packageId));
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
                'web/themes'
            );

            foreach ($originDirs as $originDir) {
                $originFullDir = $this->getSystemRootDirectory() . '/' . $originDir;

                if (!$filesystem->exists($originFullDir)) {
                    continue;
                }

                $filesystem->mirror($originFullDir, $targetBaseDir . '/' . $originDir, null, array(
                    'override'        => true,
                    'copy_on_windows' => true
                ));
            }

            $originFiles = array(
                'app/AppCache.php',
                'app/AppKernel.php',
                'app/autoload.php',
                'app/bootstrap.php.cache',
                'app/console',
                'web/app.php'
            );

            foreach ($originFiles as $originFile) {
                $originFullFile = $this->getSystemRootDirectory() . '/' . $originFile;

                if (!$filesystem->exists($originFullFile)) {
                    continue;
                }

                $filesystem->copy($originFullFile, $targetBaseDir . '/' . $originFile, true);
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        last:
        if(!empty($errors)){
            UpgradeLock::unlock();
        }
        $this->_submitRunLogForPackageUpdate($this->getKernel()->trans('备份文件'), $package, $errors);
        return $errors;
    }

    public function downloadPackageForUpdate($packageId)
    {
        UpgradeLock::lock();
        $errors = array();
        try {
            $package = $this->getCenterPackageInfo($packageId);

            if (empty($package)) {
                throw $this->createServiceException($this->getKernel()->trans('应用包#%packageId%不存在或网络超时，读取包信息失败', array('%packageId%' => $packageId)));
            }

            $filepath = $this->createAppClient()->downloadPackage($packageId);

            $this->unzipPackageFile($filepath, $this->makePackageFileUnzipDir($package));
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        if(!empty($errors)){
            UpgradeLock::unlock();
        }
        $this->_submitRunLogForPackageUpdate($this->getKernel()->trans('下载应用包'), $package, $errors);
        return $errors;
    }

    public function checkDownloadPackageForUpdate($packageId)
    {
        UpgradeLock::lock();
        $result = $this->createAppClient()->checkDownloadPackage($packageId);

        if ($result['status'] == 'ok') {
            return array();
        }

        UpgradeLock::unlock();
        return $result['errors'];
    }

    public function beginPackageUpdate($packageId, $type, $index = 0)
    {
        UpgradeLock::lock();
        $errors  = array();
        $package = $packageDir = null;
        try {
            $package = $this->getCenterPackageInfo($packageId);

            if (empty($package)) {
                throw $this->createServiceException($this->getKernel()->trans('应用包#%packageId%不存在或网络超时，读取包信息失败', array('%packageId%' => $packageId)));
            }

            $packageDir = $this->makePackageFileUnzipDir($package);
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            goto last;
        }

        if (empty($index)) {
            try {
                $this->_deleteFilesForPackageUpdate($package, $packageDir);
            } catch (\Exception $e) {
                $errors[] = $this->getKernel()->trans('删除文件时发生了错误：%getMessage%', array('%getMessage%' => $e->getMessage()));
                $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
                goto last;
            }

            try {
                $this->_replaceFileForPackageUpdate($package, $packageDir);
            } catch (\Exception $e) {
                $errors[] = $this->getKernel()->trans('复制升级文件时发生了错误：%getMessage%', array('%getMessage%' => $e->getMessage()));
                $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
                goto last;
            }
        }

        try {
            $info = $this->_execScriptForPackageUpdate($package, $packageDir, $type, $index);

            if (isset($info['index'])) {
                goto last;
            }
        } catch (\Exception $e) {
            $errors[] = $this->getKernel()->trans('执行升级/安装脚本时发生了错误：%getMessage%', array('%getMessage%' => $e->getMessage()));
            $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
            goto last;
        }

        try {
            $this->deleteCache();
        } catch (\Exception $e) {
            $cachePath  = $this->getKernel()->getParameter('kernel.root_dir') . '/cache/' . $this->getKernel()->getEnvironment();
            $errors[] = $this->getKernel()->trans('应用安装升级成功，但刷新缓存失败！请检查%cachePath%的权限', array('%cachePath%' => $cachePath));
            $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
            goto last;
        }

        try {
            $this->_refreshDefaultRoles();
        } catch (\Exception $e) {
            $errors[] = $this->getKernel()->trans('刷新默认角色权限失败! ');
            $this->createPackageUpdateLog($package, 'ROLLBACK', implode('\n', $errors));
            goto last;
        }

        if (empty($errors)) {
            $this->updateAppForPackageUpdate($package, $packageDir);
            $this->createPackageUpdateLog($package, 'SUCCESS');
            PluginUtil::refresh();
            UpgradeLock::unlock();
        }

        last:
        $this->_submitRunLogForPackageUpdate($this->getKernel()->trans('执行升级'), $package, $errors);

        if (empty($info)) {
            $result = $errors;
            UpgradeLock::unlock();
        } else {
            $result = $info;
        }
        return $result;
    }

    protected function _refreshDefaultRoles()
    {
        $this->getRoleService()->refreshRoles();
    }

    protected function deleteCache($tryCount = 0)
    {
        if ($tryCount >= 5) {
            throw $this->createServiceException("cannot delete cache.");
        }

        sleep($tryCount * 2);

        try {
            $cachePath  = $this->getKernel()->getParameter('kernel.root_dir') . '/cache';
            $filesystem = new Filesystem();
            $filesystem->remove($cachePath);
            clearstatcache(true);
            sleep(3);
            //注解需要该目录存在
            if (!$filesystem->exists($cachePath . '/annotations/topxia')) {
                $filesystem->mkdir($cachePath . '/annotations/topxia');
            }
        } catch (\Exception $e) {
            $tryCount++;
            $this->deleteCache($tryCount);
        }
    }

    public function repairProblem($token)
    {
        return $this->createAppClient()->repairProblem($token);
    }

    public function findInstallApp($code)
    {
        return $this->getAppDao()->getAppByCode($code);
    }

    public function uninstallApp($code)
    {
        $app = $this->getAppDao()->getAppByCode($code);

        if (empty($app)) {
            throw $this->createServiceException("App {$code} is not exist.");
        }

        if ($app['type'] == 'plugin') {
            $uninstallScript = realpath($this->getKernel()->getParameter('kernel.root_dir') . '/../plugins/' . ucfirst($app['code']) . '/Scripts/uninstall.php');

            if (file_exists($uninstallScript)) {
                include $uninstallScript;
                $uninstaller = new \AppUninstaller($this->getKernel());
                $uninstaller->uninstall();
            }
        } elseif ($app['type'] == 'theme') {
            $themeDir   = realpath($this->getKernel()->getParameter('kernel.root_dir') . '/../web/themes/' . strtolower($app['code']));
            $filesystem = new Filesystem();
            $filesystem->remove($themeDir);
        }

        $this->getAppDao()->deleteApp($app['id']);

        $cachePath  = $this->getKernel()->getParameter('kernel.root_dir') . '/cache/' . $this->getKernel()->getEnvironment();
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);
        $this->_refreshDefaultRoles();
    }

    public function updateAppVersion($id, $version)
    {
        $app = $this->getAppDao()->getApp($id);

        if (empty($app)) {
            throw $this->createServiceException($this->getKernel()->trans('App #%id%不存在，更新版本失败！', array('%id%' => $id)));
        }

        $this->getLogService()->info('system', 'update_app_version', $this->getKernel()->trans('强制更新应用「%appName%」版本为「%version%」', array('%appName%' => $app['name'], '%version%' => $version)));
        return $this->getAppDao()->updateApp($id, array('version' => $version));
    }

    public function getTokenLoginUrl($routingName, $params)
    {
        $appClient = $this->createAppClient();
        $result    = $appClient->getTokenLoginUrl($routingName, $params);
        return $result;
    }

    protected function _replaceFileForPackageUpdate($package, $packageDir)
    {
        $filesystem = new Filesystem();
        $filesystem->mirror("{$packageDir}/source", $this->getPackageRootDirectory($package, $packageDir), null, array(
            'override'        => true,
            'copy_on_windows' => true
        ));
    }

    protected function _execScriptForPackageUpdate($package, $packageDir, $type, $index = 0)
    {
        if (!file_exists($packageDir . '/Upgrade.php')) {
            return;
        }

        include_once $packageDir . '/Upgrade.php';
        $upgrade = new \EduSohoUpgrade($this->getKernel());

        if (method_exists($upgrade, 'setUpgradeType')) {
            $upgrade->setUpgradeType($type, $package['toVersion']);
        }

        if (method_exists($upgrade, 'update')) {
            $info = $upgrade->update($index);
            return empty($info) ? array() : $info;
        }

        return array();
    }

    protected function _deleteFilesForPackageUpdate($package, $packageDir)
    {
        if (!file_exists($packageDir . '/delete')) {
            return;
        }

        $filesystem = new Filesystem();
        $fh         = fopen($packageDir . '/delete', 'r');

        while ($filepath = fgets($fh)) {
            $fullpath = $this->getPackageRootDirectory($package, $packageDir) . '/' . trim($filepath);

            if (file_exists($fullpath)) {
                $filesystem->remove($fullpath);
            }
        }

        fclose($fh);
    }

    protected function _submitRunLogForPackageUpdate($message, $package, $errors)
    {
        $this->createAppClient()->submitRunLog(array(
            'level'       => empty($errors) ? 'info' : 'error',
            'productId'   => $package['productId'],
            'productName' => $package['product']['name'],
            'packageId'   => $package['id'],
            'type'        => $package['type'],
            'fromVersion' => empty($package['fromVersion']) ? '' : $package['fromVersion'],
            'toVersion'   => empty($package['toVersion']) ? '' : $package['toVersion'],
            'message'     => $message . (empty($errors) ? $this->trans('成功') : $this->trans('失败')),
            'data'        => empty($errors) ? '' : json_encode($errors)
        ));
    }

    protected function unzipPackageFile($filepath, $unzipDir)
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

        if ($zip->open($filepath) === true) {
            $tmpUnzipFullDir = $tmpUnzipDir . '/' . $zip->getNameIndex(0);
            $zip->extractTo($tmpUnzipDir);
            $zip->close();
            $filesystem->rename($tmpUnzipFullDir, $unzipDir);
            $filesystem->remove($tmpUnzipDir);
        } else {
            throw new \Exception($this->getKernel()->trans('无法解压缩安装包！'));
        }
    }

    protected function getPackageRootDirectory($package, $packageDir)
    {
        if ($package['product']['code'] == 'MAIN') {
            return $this->getSystemRootDirectory();
        }

        if (file_exists($packageDir . '/ThemeApp')) {
            return realpath($this->getKernel()->getParameter('kernel.root_dir') . '/../' . 'web/themes');
        }

        return realpath($this->getKernel()->getParameter('kernel.root_dir') . '/../' . 'plugins');
    }

    protected function getSystemRootDirectory()
    {
        return dirname($this->getKernel()->getParameter('kernel.root_dir'));
    }

    protected function getDownloadDirectory()
    {
        return $this->getKernel()->getParameter('topxia.disk.update_dir');
    }

    protected function getBackUpDirectory()
    {
        return $this->getKernel()->getParameter('topxia.disk.backup_dir');
    }

    protected function makePackageFileUnzipDir($package)
    {
        return $this->getDownloadDirectory() . '/' . $package['fileName'];
    }

    protected function addEduSohoMainApp()
    {
        $app = array(
            'code'          => 'MAIN',
            'name'          => $this->getKernel()->trans('EduSoho主系统'),
            'description'   => $this->getKernel()->trans('EduSoho主系统'),
            'icon'          => '',
            'version'       => System::VERSION,
            'fromVersion'   => '0.0.0',
            'developerId'   => 1,
            'developerName' => $this->getKernel()->trans('EduSoho官方'),
            'installedTime' => time(),
            'updatedTime'   => time()
        );
        $this->getAppDao()->addApp($app);
    }

    protected function updateAppForPackageUpdate($package, $packageDir)
    {
        $newApp = array(
            'code'              => $package['product']['code'],
            'name'              => $package['product']['name'],
            'description'       => $package['product']['description'],
            'icon'              => $package['product']['icon'],
            'version'           => $package['toVersion'],
            'fromVersion'       => $package['fromVersion'],
            'developerId'       => $package['product']['developerId'],
            'developerName'     => $package['product']['developerName'],
            'edusohoMaxVersion' => $package['edusohoMaxVersion'],
            'edusohoMinVersion' => $package['edusohoMinVersion'],
            'updatedTime'       => time()
        );

        if (file_exists($packageDir . '/ThemeApp')) {
            $newApp['type'] = 'theme';
        } else {
            $newApp['type'] = 'plugin';
        }

        $app = $this->getAppDao()->getAppByCode($package['product']['code']);

        if (empty($app)) {
            $newApp['installedTime'] = time();
            return $this->getAppDao()->addApp($newApp);
        }

        return $this->getAppDao()->updateApp($app['id'], $newApp);
    }

    /**
     * @return CloudAppDaoImpl
     */
    protected function getAppDao()
    {
        return $this->createDao('CloudPlatform.CloudAppDao');
    }

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform.CloudAppLogDao');
    }

    protected function createAppClient()
    {
        if (!isset($this->client)) {
            $cloud     = $this->getSettingService()->get('storage', array());
            $developer = $this->getSettingService()->get('developer', array());

            $options = array(
                'accessKey' => empty($cloud['cloud_access_key']) ? null : $cloud['cloud_access_key'],
                'secretKey' => empty($cloud['cloud_secret_key']) ? null : $cloud['cloud_secret_key'],
                'apiUrl'    => empty($developer['app_api_url']) ? null : $developer['app_api_url'],
                'debug'     => empty($developer['debug']) ? false : true
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

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    /**
     * @return RoleServiceImpl
     */
    protected function getRoleService()
    {
        return $this->createService('Permission:Role.RoleService');
    }
}
