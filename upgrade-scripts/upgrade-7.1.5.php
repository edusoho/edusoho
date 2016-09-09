<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->batchUpdate($index);
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    protected function batchUpdate($index)
    {
        $connection = $this->getConnection();

        //7.1.1安装包缺少字段
        if (!$this->isTableExist('file_used')) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS  `file_used` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `type` varchar(32) NOT NULL,
                  `fileId` int(11) NOT NULL COMMENT 'upload_files id',
                  `targetType` varchar(32) NOT NULL,
                  `targetId` int(11) NOT NULL,
                  `createdTime` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `file_used_type_targetType_targetId_index` (`type`,`targetType`,`targetId`),
                  KEY `file_used_type_targetType_targetId_fileId_index` (`type`,`targetType`,`targetId`,`fileId`),
                  KEY `file_used_fileId_index` (`fileId`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
        }

        if (!$this->isFieldExist('upload_files', 'useType')) {
            $connection->exec("ALTER TABLE upload_files  ADD `useType` varchar(64) DEFAULT NULL COMMENT '文件使用的模块类型'  AFTER  `targetType`;");
        }

        if ($this->isFieldExist('cash_orders', 'payment')) {
            $connection->exec("ALTER TABLE `cash_orders` CHANGE `payment` `payment` VARCHAR(32) NOT NULL DEFAULT 'none';");
        }

        if ($this->isFieldExist('cash_flow', 'payment')) {
            $connection->exec("ALTER TABLE `cash_flow` CHANGE `payment` `payment` VARCHAR(32) NULL DEFAULT ''");
        }

        //解决7.1.1安装包缺少字段引起的注册问题
        $userSql      = "select up.id, u.id as uid from user_profile as up left join user as u on up.id=u.id where up.id<15";
        $userProfiles = $connection->fetchAll($userSql, array());

        foreach ($userProfiles as $profile) {
            if (empty($profile['uid'])) {
                $delSql = "delete from user_profile where id=".$profile['id'];
                $connection->exec($delSql);
            }
        }

        if (!$this->isFieldExist('user', 'locale')) {
            $connection->exec("ALTER TABLE `user` ADD `locale` VARCHAR(20) AFTER `payPasswordSalt`;");
        }

        if (!$this->isFieldExist('cloud_app', 'edusohoMinVersion')) {
            $connection->exec("ALTER TABLE `cloud_app` ADD `edusohoMinVersion`  VARCHAR(32) NOT NULL DEFAULT '0.0.0' COMMENT '依赖Edusoho的最小版本';");
        }

        if (!$this->isFieldExist('cloud_app', 'edusohoMaxVersion')) {
            $connection->exec("ALTER TABLE `cloud_app` ADD `edusohoMaxVersion`  VARCHAR(32) NOT NULL DEFAULT 'up' COMMENT '依赖Edusoho的最大版本';");
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

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @return \Topxia\Service\System\Impl\SettingServiceImpl
     */
    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return \Topxia\Service\Common\Connection
     */
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
