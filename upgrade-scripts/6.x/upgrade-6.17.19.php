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

    private function updateScheme()
    {
        if (!$this->isFieldExist('announcement', 'copyId')) {
            $this->getConnection()->exec("ALTER TABLE announcement ADD copyId INT(11) NOT NULL DEFAULT '0' COMMENT '复制的公告ID';");
        }

        if (!$this->isTableExist('im_conversation')) {
            $this->getConnection()->exec("
                CREATE TABLE `im_conversation` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `no` varchar(64) NOT NULL COMMENT 'IM云端返回的会话id',
                    `memberIds` text NOT NULL COMMENT '会话中用户列表(用户id按照小到大排序，竖线隔开)',
                    `memberHash` varchar(32) NOT NULL DEFAULT '' COMMENT 'memberIds字段的hash值，用于优化查询',
                    `createdTime` int(10) UNSIGNED NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COMMENT='IM云端会话记录表';
            ");
        }

        if (!$this->isTableExist('im_my_conversation')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `im_my_conversation` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `no` varchar(64) NOT NULL,
                    `userId` int(10) UNSIGNED NOT NULL,
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                    `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='用户个人的会话列表';
            ");
        }
    }

    private function batchUpdate($index)
    {
        if ($index === 0) {
            $this->updateScheme();
            return array(
                'index'    => 1,
                'message'  => '正在升级数据...',
                'progress' => 0
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
                'progress' => 0
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
