<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();

        try {
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();

            $this->updateCrontabSetting();

            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());
    }

    private function batchUpdate($index)
    {
        if (!$this->isFieldExist('announcement', 'copyId')) {
            $this->getConnection()->exec("ALTER TABLE announcement ADD copyId INT(11) NOT NULL DEFAULT '0' COMMENT '复制的公告ID';");
        }

        if ($index === 0) {
            return array(
                'index'    => 1,
                'message'  => '正在升级数据...',
                'progress' => 4.4
            );
        }

        $conditions = array(
            'storage'  => 'cloud',
            'globalId' => 0
        );
        $total = $this->getUploadFileService()->searchFileCount($conditions);

        $maxPage = ceil($total / 100) ? ceil($total / 100) : 1;

        $this->getCloudFileService()->synData($conditions);

        if ($index <= $maxPage) {
            return array(
                'index'    => $index + 1,
                'message'  => '正在升级数据...',
                'progress' => 4.4
            );
        }
    }

    private function deleteDir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);

        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir."/".$file;

                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deleteDir($fullpath);
                }
            }
        }

        closedir($dh);

//删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    protected function getAppService()
    {
        return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }

    private function updateCrontabSetting()
    {
        $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    private function getCloudFileService()
    {
        return ServiceKernel::instance()->createService('CloudFile.CloudFileService');
    }

    private function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
