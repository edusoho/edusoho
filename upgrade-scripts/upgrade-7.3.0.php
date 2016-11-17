<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir') . "../web/install");
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

    private function updateScheme()
    {
        $connection = $this->getConnection();

        if (!$this->isFieldExist('course_member', 'finishedTime')) {
            $connection->exec("ALTER TABLE `course_member` ADD `finishedTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '完成课程时间' AFTER `isLearned`");
        }

        $now = time();
        $connection->exec("UPDATE `course_member` SET finishedTime = {$now} WHERE isLearned = 1");
        if (!$this->isTableExist('subtitle')) {
            $connection->exec("CREATE TABLE `subtitle` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL COMMENT '字幕名称',
                `subtitleId` int(10) UNSIGNED NOT NULL COMMENT 'subtitle的uploadFileId',
                `mediaId` int(10) UNSIGNED NOT NULL COMMENT 'video/audio的uploadFileId',
                `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
                `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) COMMENT='字幕关联表';");
        }

        if ($this->isFieldExist('upload_files', 'type')) {
            $connection->exec("ALTER TABLE `upload_files` CHANGE COLUMN `type` `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型';");
        }

        if ($this->isFieldExist('upload_file_inits', 'type')) {
            $connection->exec("ALTER TABLE `upload_file_inits` CHANGE COLUMN `type` `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型';");
        }

        $connection->exec("update course_member cm1 LEFT JOIN classroom_member cm2 ON cm1.classroomId = cm2.classroomId and cm1.userId=cm2.userId and cm1.joinedType='classroom' set cm1.levelId=cm2.levelId where cm1.joinedType='classroom'");

        $setting = $this->getSettingService()->get('user_partner');
        if(!empty($setting['mode']) && $setting['mode'] == 'phpwind') {
            $setting['mode'] = 'default';
            $setting = $this->getSettingService()->set('user_partner', $setting);
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
