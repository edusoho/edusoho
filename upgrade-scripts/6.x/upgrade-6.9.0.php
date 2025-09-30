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

        if (!$this->isFieldExist('course_lesson', 'suggestHours')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD COLUMN `suggestHours` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '建议学习时长'");
            $connection->exec("UPDATE `course_lesson` SET `suggestHours` = CEIL(length/3600) WHERE type IN('video','audio') AND length is not Null");
            $connection->exec("UPDATE `course_lesson` SET `suggestHours` = 1 WHERE type IN('video','audio')  AND  length is Null");
            $connection->exec("UPDATE `course_lesson` SET `suggestHours` = 2 WHERE type NOT IN('video','audio') AND  length is Null");
            $connection->exec("UPDATE `course_lesson` SET `suggestHours` = CEIL(length/60) WHERE type IN('live') AND length is not Null");
        }

        if ($this->isFieldExist('testpaper_result', 'passedStatus')) {
            $connection->exec("alter table testpaper_result modify passedStatus enum('none','excellent','good','passed','unpassed')");
        }

        if (!$this->isTableExist('card')) {
            $connection->exec("CREATE TABLE `card` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `cardId` varchar(255)  NOT NULL DEFAULT '' COMMENT '卡的ID',
                  `cardType` varchar(255) NOT NULL DEFAULT '' COMMENT '卡的类型',
                  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
                  `useTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
                  `status` ENUM('used','receive','invalid','deleted') NOT NULL DEFAULT 'receive' COMMENT '使用状态',
                  `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '领取时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
        }

        if (!$this->isFieldExist('course_lesson', 'testMode')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD `testMode` ENUM('normal', 'realTime') NULL DEFAULT 'normal' COMMENT '考试模式'");
        }

        if (!$this->isFieldExist('course_lesson', 'testStartTime')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD `testStartTime` INT(10) NULL DEFAULT '0' COMMENT '实时考试开始时间'");
        }

        if (!$this->isFieldExist('course', 'buyable')) {
            $connection->exec("ALTER TABLE course ADD buyable tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放购买' AFTER `status`;");
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
