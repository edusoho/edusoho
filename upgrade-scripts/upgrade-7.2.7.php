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

        if ($this->isTableExist('im_my_conversation')) {
            $connection->exec("drop table im_my_conversation");
        }

        if ($this->isFieldExist('course', 'conversationId')) {
            $connection->exec("ALTER TABLE course CHANGE conversationId convNo VARCHAR(32) NOT NULL DEFAULT ''  COMMENT '课程会话ID';");
            $connection->exec("UPDATE course SET `convNo` = '' WHERE `convNo` = '0';");
        }

        if ($this->isFieldExist('classroom', 'conversationId')) {
            $connection->exec("ALTER TABLE classroom CHANGE conversationId convNo VARCHAR(32) NOT NULL DEFAULT ''  COMMENT '班级会话ID';");
            $connection->exec("UPDATE classroom SET `convNo` = '' WHERE `convNo` = '0';");
        }

        if (!$this->isTableExist('im_member')) {
            $connection->exec("
                CREATE TABLE `im_member` (
                  `id` int(10) NOT NULL AUTO_INCREMENT,
                  `convNo` varchar(32) NOT NULL COMMENT '会话ID',
                  `targetId` int(10) NOT NULL,
                  `targetType` varchar(15) NOT NULL,
                  `userId` int(10) NOT NULL DEFAULT '0',
                  `createdTime` int(10) DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '会话用户表';
            ");
        }

        //后台IM设置权限
        $sql    = "select * from role where code='ROLE_SUPER_ADMIN';";
        $result = $this->connection->fetchAssoc($sql);
        if ($result) {
            $data = array_merge(json_decode($result['data']), array('admin_app_im'));
            $connection->exec("update role set data='".json_encode($data)."' where code='ROLE_SUPER_ADMIN';");
        }

        if (!$this->isFieldExist('im_conversation', 'targetType')) {
            $connection->exec("ALTER TABLE `im_conversation` ADD `targetType` VARCHAR(16) NOT NULL DEFAULT '' AFTER `no`");
        }

        if (!$this->isFieldExist('im_conversation', 'targetId')) {
            $connection->exec("ALTER TABLE `im_conversation` ADD `targetId` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `targetType`");
        }

        if (!$this->isFieldExist('im_conversation', 'title')) {
            $connection->exec("ALTER TABLE `im_conversation` ADD `title` VARCHAR(255) NOT NULL DEFAULT ''");
        }

        if ($this->isFieldExist('course', 'convNo')) {
            $connection->exec("alter table `course` drop column convNo");
        }

        if ($this->isFieldExist('classroom', 'convNo')) {
            $connection->exec("alter table `classroom` drop column convNo");
        }

        if (!$this->isIndexExist('im_conversation', 'no')) {
            $connection->exec("ALTER TABLE `im_conversation` ADD UNIQUE(`no`);");
        }

        if (!$this->isIndexExist('im_conversation', 'targetId')) {
            $connection->exec("ALTER TABLE `im_conversation` ADD INDEX targetId ( `targetId`);");
        }

        if (!$this->isIndexExist('im_conversation', 'targetType')) {
            $connection->exec("ALTER TABLE `im_conversation` ADD INDEX targetType ( `targetType`);");
        }

        if (!$this->isIndexExist('im_member', 'convno_userId')) {
            $connection->exec("ALTER TABLE `im_member` ADD INDEX convno_userId ( `convNo`, `userId` );");
        }

        if (!$this->isIndexExist('im_member', 'userId_targetType')) {
            $connection->exec("ALTER TABLE `im_member` ADD INDEX userId_targetType ( `userId`,`targetType` );");
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

    protected function isIndexExist($table, $indexName)
    {
        $sql    = "show index from `{$table}`  where Key_name='{$indexName}';";
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

    private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    /**
     * @return \Permission\Service\Role\Impl\RoleServiceImpl
     */
    private function getRoleService()
    {
        return ServiceKernel::instance()->createService('Permission:Role.RoleService');
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
