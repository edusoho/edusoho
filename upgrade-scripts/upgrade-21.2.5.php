<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
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
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
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
            'addNewTables',
            'addReviewColumn',
            'addThreadColumn',
            'addThreadPostColumn',
            'addCourseThreadColumn',
            'addCourseThreadPostColumn',
            'addGroupThreadColumn',
            'addGroupThreadPostColumn',
            'addCourseNoteColumn',
            'alterTableNotificationBatch',
            'addSyncRecordJob',
            'setNotificationSetting',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');

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

    public function addNewTables()
    {
        if (!$this->isTableExist('user_content_audit')) {
            $this->getConnection()->exec("
            CREATE TABLE `user_content_audit` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `targetType` varchar(32) NOT NULL DEFAULT '' COMMENT '内容类型',
              `targetId` int(10) NOT NULL COMMENT '内容ID',
              `author` int(11) NOT NULL COMMENT '作者ID',
              `content` mediumtext COMMENT '内容',
              `sensitiveWords` varchar(2048) DEFAULT '' COMMENT '敏感词',
              `auditor` int(11) DEFAULT NULL COMMENT '最后一次审核人',
              `status` varchar(32) NOT NULL DEFAULT '' COMMENT '当前审核状态',
              `auditTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
              `createdTime` int(11) unsigned NOT NULL,
              `updatedTime` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->isTableExist('user_content_audit_record')) {
            $this->getConnection()->exec("
                CREATE TABLE `user_content_audit_record` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `auditId` int(11) unsigned NOT NULL COMMENT '审核表ID',
                      `author` int(11) NOT NULL COMMENT '作者',
                      `content` mediumtext NOT NULL COMMENT '审核内容',
                      `sensitiveWords` varchar(2048) NOT NULL DEFAULT '' COMMENT '敏感词',
                      `auditor` int(11) NOT NULL COMMENT '审核人',
                      `status` varchar(32) NOT NULL DEFAULT '' COMMENT '状态',
                      `originStatus` varchar(32) NOT NULL DEFAULT '' COMMENT '原审核状态',
                      `auditTime` int(11) unsigned NOT NULL COMMENT '审核时间',
                      `createdTime` int(11) unsigned NOT NULL,
                      `updatedTime` int(11) unsigned NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->isTableExist('report_audit')) {
            $this->getConnection()->exec("CREATE TABLE `report_audit` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `module` varchar(32) NOT NULL DEFAULT '' COMMENT '举报目标模块',
              `targetType` varchar(32) NOT NULL DEFAULT '' COMMENT '举报目标类型',
              `targetId` int(11) NOT NULL COMMENT '举报目标id',
              `content` mediumtext NOT NULL COMMENT '举报正文',
              `author` int(11) NOT NULL COMMENT '作者',
              `reportCount` int(11) NOT NULL DEFAULT '0' COMMENT '被举报次数',
              `reportTags` varchar(1024) NOT NULL DEFAULT '' COMMENT '举报标签',
              `auditor` int(11) DEFAULT '0' COMMENT '审核人',
              `status` varchar(32) NOT NULL DEFAULT '' COMMENT '审核状态',
              `auditTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
              `createdTime` int(11) DEFAULT NULL,
              `updatedTime` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->isTableExist('report_audit_record')) {
            $this->getConnection()->exec("CREATE TABLE `report_audit_record` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `auditId` int(11) unsigned NOT NULL COMMENT '审核表ID',
              `content` mediumtext NOT NULL COMMENT '审核正文',
              `author` int(11) NOT NULL COMMENT '作者',
              `reportTags` varchar(1024) NOT NULL DEFAULT '' COMMENT '举报标签',
              `auditor` int(11) NOT NULL COMMENT '审核者',
              `status` varchar(32) NOT NULL DEFAULT '' COMMENT '审核状态',
              `originStatus` varchar(32) NOT NULL DEFAULT '' COMMENT '原审核状态',
              `auditTime` int(11) unsigned NOT NULL COMMENT '审核时间',
              `createdTime` int(11) unsigned NOT NULL,
              `updatedTime` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->isTableExist('report_record')) {
            $this->getConnection()->exec("CREATE TABLE `report_record` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `auditId` int(11) unsigned NOT NULL COMMENT '审核ID',
              `targetType` varchar(32) NOT NULL DEFAULT '' COMMENT '举报目标类型',
              `targetId` int(11) NOT NULL COMMENT '举报目标id',
              `reporter` int(11) unsigned NOT NULL COMMENT '举报者',
              `content` mediumtext NOT NULL COMMENT '举报正文',
              `author` int(11) NOT NULL COMMENT '作者',
              `reportTags` varchar(1024) NOT NULL DEFAULT '' COMMENT '举报标签',
              `auditTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->isTableExist('wechat_subscribe_record')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `wechat_subscribe_record` (
                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                  `toId` varchar(64) NOT NULL DEFAULT '' COMMENT '用户openId',
                  `templateCode` varchar(64) NOT NULL DEFAULT '' COMMENT '模板code',
                  `templateType` varchar(32) NOT NULL DEFAULT '' COMMENT '模板类型（一次性、长期）',
                  `isSend` tinyint NOT NULL DEFAULT 0 COMMENT '是否已发送',
                  `createdTime` int unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  key `toId` (`toId`),
                  key `templateCode` (`templateCode`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='微信订阅记录表';
            ");
        }

        return 1;
    }

    public function addReviewColumn()
    {
        if (!$this->isFieldExist('review', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `review` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `meta`;");
        }
        return 1;
    }

    public function addThreadColumn()
    {
        if (!$this->isFieldExist('thread', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `thread` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `status`;");
        }
        return 1;
    }

    public function addThreadPostColumn()
    {
        if (!$this->isFieldExist('thread_post', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `thread_post` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `targetId`;");
        }
        return 1;
    }

    public function addCourseThreadColumn()
    {
        if (!$this->isFieldExist('course_thread', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `course_thread` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `type`;");
        }
        return 1;
    }

    public function addCourseThreadPostColumn()
    {
        if (!$this->isFieldExist('course_thread_post', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `course_thread_post` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `userId`;");
        }
        return 1;
    }

    public function addGroupThreadColumn()
    {
        if (!$this->isFieldExist('groups_thread', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `groups_thread` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `status`;");
        }
        return 1;
    }

    public function addGroupThreadPostColumn()
    {
        if (!$this->isFieldExist('groups_thread_post', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `groups_thread_post` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `userId`;");
        }
        return 1;
    }

    public function addCourseNoteColumn()
    {
        if (!$this->isFieldExist('course_note', 'auditStatus')) {
            $this->getConnection()->exec("ALTER TABLE `course_note` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `userId`;");
        }
        return 1;
    }

    public function alterTableNotificationBatch()
    {
        if (!$this->isFieldExist('notification_batch', 'source')) {
            $this->getConnection()->exec("
                ALTER TABLE `notification_batch` ADD `source` varchar(32) NOT NULL DEFAULT '' COMMENT '通知来源' AFTER `status`;
            ");
        }

        if (!$this->isFieldExist('notification_batch', 'smsEventId')) {
            $this->getConnection()->exec("
                ALTER TABLE `notification_batch` ADD `smsEventId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'smsEventId' AFTER `eventId`;
            ");
        }

        if ($this->isFieldExist('notification_batch', 'source')) {
            $this->getConnection()->exec("
                UPDATE `notification_batch` SET `source` = 'wechat_template' where `source` = '';
            ");
        }

        return 1;
    }

    public function addSyncRecordJob()
    {
        if (!empty($this->getSchedulerService()->getJobByName('WeChatSubscribeRecordSynJob'))) {
            $this->logger('info', "定时任务已存在，直接跳过");
            return 1;
        }

        $currentTime = time();
        $expression = rand(0, 30).'/30 * * * *';
        $this->getConnection()->exec("
            INSERT INTO `biz_scheduler_job` (
                `name`,
                `expression`,
                `class`,
                `args`,
                `priority`,
                `next_fire_time`,
                `misfire_threshold`,
                `misfire_policy`,
                `enabled`,
                `creator_id`,
                `updated_time`,
                `created_time`
            ) VALUES
            (
                'WeChatSubscribeRecordSynJob',
                '{$expression}',
                'Biz\\\\WeChat\\\\Job\\\\WeChatSubscribeRecordSynJob',
                '',
                '100',
                '{$currentTime}',
                '300',
                'missed',
                '1',
                '0',
                '{$currentTime}',
                '{$currentTime}'
            );
        ");

        return 1;
    }

    public function setNotificationSetting()
    {
        $setting = $this->getSettingService()->get('wechat', []);
        $notificationSetting = $this->getSettingService()->get('wechat_notification', []);
        if (!empty($setting['wechat_notification_enabled']) && empty($notificationSetting)) {
            $this->getSettingService()->set('wechat_notification', ['notification_type' => 'serviceFollow']);
        }

        return 1;
    }

    /**
     * @return \Biz\System\Service\CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return \Biz\Role\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getTableCount($table)
    {
        $sql = "select count(*) from `{$table}`;";

        return $this->getConnection()->fetchColumn($sql) ?: 0;
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
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

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
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

    protected function getSettingService()
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
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
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
