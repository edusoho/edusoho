<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    const TOTAL_ZIP_NUM = 24;

    protected $pageSize = 1;

    private $file_download_rate_file = 'file_download_rate_8614.txt';

    private $kernel;

    public function __construct($biz)
    {
        parent::__construct($biz);
        $this->kernel = \Topxia\Service\Common\ServiceKernel::instance();
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'downloadUpgradePackage'
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if (1 == $page) {
            ++$step;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    public function downloadUpgradePackage($page)
    {
        $dir = $this->kernel->getParameter('kernel.root_dir') . '/data/upgrade';
        $packageDir = $this->kernel->getParameter('kernel.root_dir') . '/data/upgrade/php74';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';
        $filesystem = new Filesystem();
        $this->generateFile($filesystem, $dir);


        if ($page == self::TOTAL_ZIP_NUM + 1) {
            //delete
            if (!file_exists($packageDir.'/delete')) {
                return;
            }
            $fh = fopen($packageDir.'/delete', 'r');
            while ($filepath = fgets($fh)) {
                $fullpath = $edusohoDir.trim($filepath);
                if (file_exists($fullpath)) {
                    $filesystem->remove($fullpath);
                }
            }
            fclose($fh);
            //update
            $filesystem->mirror("{$packageDir}/source", $edusohoDir, null, array(
                'override' => true,
                'copy_on_windows' => true,
            ));
            @unlink($dir . '/' . $this->file_download_rate_file);
            return 1;
        }
        if (!file_exists($packageDir)) {
            $filesystem->mkdir($packageDir);
        }
        $targetPath = $packageDir. "/" .$page.".zip";

        $url = 'http://download-devtest.codeages.net/edusoho8614-php74/v1-' . sprintf("%02d", $page).".zip";
        file_put_contents($targetPath, file_get_contents($url));

        $zip = new \ZipArchive();
        if ($zip->open($targetPath) === true) {
            $this->logger('warning', 'download file from :' . $url );
            $this->logger('warning', $targetPath . ' file size is :' . filesize($targetPath));
            $zip->extractTo($packageDir);
            $zip->close();
            file_put_contents($dir . '/' . $this->file_download_rate_file, $page);
        } else {
            throw new \Exception('无法解压缩安装包！');
        }

        return $this->getNextPage(self::TOTAL_ZIP_NUM + 1, $page);
    }

    protected function _deleteFilesForPackageUpdate($package, $packageDir)
    {
        if (!file_exists($packageDir.'/delete')) {
            return;
        }

        $filesystem = new Filesystem();
        $fh = fopen($packageDir.'/delete', 'r');

        while ($filepath = fgets($fh)) {
            $fullpath = $this->getPackageRootDirectory($package, $packageDir).'/'.trim($filepath);

            if (file_exists($fullpath)) {
                $filesystem->remove($fullpath);
            }
        }

        fclose($fh);
    }

    /**
     * @param $filesystem Filesystem
     * @param $dir
     */
    protected function generateFile($filesystem, $dir)
    {
        if (!$filesystem->exists($dir)) {
            $filesystem->mkdir($dir);
        }
        $cacheRateFile = $dir . '/' . $this->file_download_rate_file;
        if (!file_exists($cacheRateFile)) {
            $filesystem->touch($cacheRateFile);
        }
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getLastPage($count)
    {
        return ceil($count / $this->pageSize);
    }

    protected function getNextPage($count, $currentPage)
    {
        $diff = $this->getLastPage($count) - $currentPage;
        return $diff > 0 ? $currentPage + 1 : 0;
    }

    protected function getStart($page)
    {
        return ($page - 1) * $this->pageSize;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;

        return array($step, $page);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    abstract public function update();

    protected function logger($level, $message)
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
    }

    /**
     * @return \Biz\DiscoveryColumn\Service\DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\System\Service\H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
