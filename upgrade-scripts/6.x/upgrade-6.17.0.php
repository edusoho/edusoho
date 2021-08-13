<?php

use Topxia\Service\Util\PluginUtil;
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
        if (!$this->isFieldExist('upload_files', 'globalId')) {
            $this->getConnection()->exec("ALTER TABLE `upload_files` ADD `globalId` VARCHAR(32) NOT NULL DEFAULT '0' COMMENT '云文件ID' AFTER `id`;");
        }

        if (!$this->isFieldExist('upload_files', 'status')) {
            $this->getConnection()->exec("ALTER TABLE `upload_files` ADD `status` ENUM('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态' AFTER `length`;");
        }

        if ($this->isFieldExist('upload_files', 'size')) {
            $this->getConnection()->exec("ALTER TABLE `upload_files` CHANGE `size` `fileSize` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文件大小';");
        }

        if (!$this->isTableExist('upload_files_collection')) {
            $this->getConnection()->exec("CREATE TABLE `upload_files_collection` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `fileId` int(10) unsigned NOT NULL COMMENT '文件Id',
          `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏者',
          `createdTime` int(10) unsigned NOT NULL,
          `updatedTime` INT(10) unsigned NULL DEFAULT '0'  COMMENT '更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文件收藏表';");
        }

        if (!$this->isTableExist('upload_files_tag')) {
            $this->getConnection()->exec("CREATE TABLE `upload_files_tag` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
              `fileId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件ID',
              `tagId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件与标签的关联表'");
        }

        if (!$this->isTableExist('upload_files_share_history')) {
            $this->getConnection()->exec("CREATE TABLE `upload_files_share_history` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
             `sourceUserId` int(10) NOT NULL COMMENT '分享用户的ID',
             `targetUserId` int(10) NOT NULL COMMENT '被分享的用户的ID',
             `isActive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '',
             `createdTime` int(10) DEFAULT '0' COMMENT '创建时间',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }

        if (!$this->isTableExist('upload_file_inits')) {
            $this->getConnection()->exec("CREATE TABLE `upload_file_inits` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `globalId` varchar(32) NOT NULL DEFAULT '0' COMMENT '云文件ID',
              `status` ENUM('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态',
              `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
              `targetId` int(11) NOT NULL COMMENT '所存目标id',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型',
              `filename` varchar(1024) NOT NULL DEFAULT '',
              `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
              `fileSize` bigint(20) NOT NULL DEFAULT '0',
              `etag` VARCHAR( 256 ) NOT NULL DEFAULT  '',
              `length` INT UNSIGNED NOT NULL DEFAULT  '0',
              `convertHash` varchar(256) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
              `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none',
              `metas` text,
              `metas2` TEXT NULL DEFAULT NULL,
              `type` ENUM(  'document',  'video',  'audio',  'image',  'ppt',  'flash', 'other' ) NOT NULL DEFAULT 'other',
              `storage` enum('local','cloud') NOT NULL,
              `convertParams` TEXT NULL COMMENT  '文件转换参数',
              `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
              `updatedTime` int(10) unsigned DEFAULT '0',
              `createdUserId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `hashId` (`hashId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");

            $sql    = "select max(id) as maxId from upload_files;";
            $result = $this->getConnection()->fetchAssoc($sql);

            $start = $result['maxId'] + 1000;
            $this->getConnection()->exec("alter table upload_file_inits AUTO_INCREMENT = {$start};");
        }

        $this->getConnection()->exec("ALTER TABLE `upload_files` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL;");

        if (!$this->isFieldExist('classroom', 'conversationId')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` ADD `conversationId` varchar(255) NOT NULL DEFAULT '0';");
        }

        if (!$this->isFieldExist('course', 'conversationId')) {
            $this->getConnection()->exec("ALTER TABLE `course` ADD `conversationId` varchar(255) NOT NULL DEFAULT '0';");
        }

        if (!$this->isTableExist('cloud_data')) {
            $this->getConnection()->exec('CREATE TABLE `cloud_data` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `body` text NOT NULL,
              `timestamp` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              `updatedTime` int(10) unsigned NOT NULL,
              `createdUserId` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        if (!$this->isFieldExist('file', 'uploadFileId')) {
            $this->getConnection()->exec("ALTER TABLE `file` ADD `uploadFileId` INT(10) NULL AFTER `createdTime`;");

            $this->getConnection()->exec("insert into `file` (groupId, userId, uri, size, createdTime,mime, uploadFileId) select (select id from file_group where name='默认文件组') as groupId, createdUserId as userId, concat('public://',hashId) as uri, fileSize as size, createdTime, type as mime, id as uploadFileId from upload_files where isPublic=1;");

            $this->getConnection()->exec("delete from upload_files where isPublic=1;");
        }

        $this->getConnection()->exec("ALTER TABLE  `upload_files` CHANGE  `convertParams`  `convertParams` TEXT NULL COMMENT '文件转换参数';");
    }

    private function batchUpdate($index)
    {
        if ($index === 0) {
            $this->updateScheme();
            $this->uninstallApp('MaterialLib');
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
        $total   = $this->getUploadFileService()->searchFileCount($conditions);
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

    protected function uninstallApp($name)
    {
        if (!$name) {
            throw new \RuntimeException("插件名称不能为空！");
        }

        $app = $this->getAppService()->getAppByCode($name);

        if (!empty($app)) {
            $pluginDir = dirname(ServiceKernel::instance()->getParameter('kernel.root_dir')).'/plugins/'.$name;

            if (is_dir($pluginDir)) {
                $this->deleteDir($pluginDir);
            }

            $app = $this->getAppService()->uninstallApp($name);

            PluginUtil::refresh();
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
