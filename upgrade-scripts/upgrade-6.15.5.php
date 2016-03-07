<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();

            $this->updateCrontabSetting();
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
        $connection = $this->getConnection();

        if (!$this->isFieldExist('user', 'updatedTime')) {
            $connection->exec("ALTER TABLE `user` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间';");
            $connection->exec("ALTER TABLE `user` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if ($this->isFieldExist('user', 'createdTime')) {
            $connection->exec("UPDATE `user` SET  `updatedTime` = `createdTime` where updatedTime = 0;");
        }

        if (!$this->isFieldExist('course', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间';");
            $connection->exec("ALTER TABLE `course` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if ($this->isFieldExist('course', 'createdTime')) {
            $connection->exec("UPDATE `course` SET  `updatedTime` = `createdTime` where updatedTime = 0;");
        }

        if (!$this->isFieldExist('course_lesson', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间';");
            $connection->exec("ALTER TABLE `course_lesson` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if ($this->isFieldExist('course_lesson', 'updatedTime')) {
            $connection->exec("UPDATE `course_lesson` SET  `updatedTime` = `createdTime` where updatedTime = 0;");
        }

        if (!$this->isFieldExist('course_thread', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course_thread` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间';");
            $connection->exec("ALTER TABLE `course_thread` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if ($this->isFieldExist('course_thread', 'updatedTime')) {
            $connection->exec("UPDATE `course_thread` SET  `updatedTime` = `createdTime` where updatedTime = 0;");
        }

        if (!$this->isFieldExist('thread', 'updateTime')) {
            $connection->exec("ALTER TABLE `thread` ADD INDEX(`updateTime`);");
        }

        if (!$this->isFieldExist('groups_thread', 'updatedTime')) {
            $connection->exec("ALTER TABLE `groups_thread` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间';");
            $connection->exec("ALTER TABLE `groups_thread` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if ($this->isFieldExist('groups_thread', 'updatedTime')) {
            $connection->exec("UPDATE `groups_thread` SET  `updatedTime` = `createdTime` where updatedTime = 0;");
        }

        if (!$this->isFieldExist('article', 'updatedTime')) {
            $connection->exec("ALTER TABLE `article` ADD INDEX(`updatedTime`);");
        }
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
