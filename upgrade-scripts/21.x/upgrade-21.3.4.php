<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    private $perPageCount = 20;

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
            'initMultiClassTable',
            'initMultiClassJob',
            'modifyEducationAdminRole',
            'initMultiClassTime',
            'initMultiClassSetting',
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

    public function initMultiClassTable()
    {
        if (!$this->isTableExist('multi_class_group')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `multi_class_group` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(64) NOT NULL COMMENT '分组名称',
                    `assistant_id` int(10) unsigned NOT NULL COMMENT '助教ID',
                    `multi_class_id` int(10) unsigned NOT NULL COMMENT '班课ID',
                    `course_id` int(10) unsigned NOT NULL default 0 COMMENT '课程ID',
                    `student_num` int(10) unsigned NOT NULL default 0 COMMENT '学员数量',
                    `seq` int(10) NOT NULL DEFAULT 0 COMMENT '分组序号',
                    `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课分组';
            ");
            $this->logger('info', '新增multi_class_group');
        }

        if (!$this->isTableExist('multi_class_live_group')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `multi_class_live_group` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `group_id` int(10) unsigned NOT NULL COMMENT '分组ID',
                    `live_code` varchar(64) NOT NULL default '' COMMENT '直播分组Code',
                    `live_id` int(10) unsigned NOT NULL default 0 COMMENT '直播ID',
                    `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课分组';
            ");
            $this->logger('info', '新增multi_class_live_group');
        }

        if (!$this->isTableExist('multi_class_record')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `multi_class_record` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
                    `assistant_id` int(10) unsigned NOT NULL COMMENT '助教ID',
                    `multi_class_id` int(10) NOT NULL COMMENT '班课ID',
                    `data` text COMMENT 'json格式信息',
                    `sign` varchar(64) not null default '' COMMENT '唯一标识',
                    `is_push` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否上传',
                    `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课上报记录';
            ");
            $this->logger('info', '新增multi_class_record');
        }

        if (!$this->isFieldExist('assistant_student', 'group_id')) {
            $this->getConnection()->exec("ALTER TABLE `assistant_student` ADD COLUMN `group_id` int(10) not null default 0 COMMENT '分组ID' after `multiClassId`;");
        }

        if (!$this->isFieldExist('multi_class', 'start_time')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `start_time` int(10) not null default '0' COMMENT '开课时间' after `productId`;");
        }

        if (!$this->isFieldExist('multi_class', 'end_time')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `end_time` int(10) not null default '0' COMMENT '结课时间' after `start_time`;");
        }

        if (!$this->isFieldExist('multi_class', 'type')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `type` varchar(32) not null default 'normal' COMMENT '班课或者分组班课(normal, group)' after `end_time`;");
        }

        if (!$this->isFieldExist('multi_class', 'service_num')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `service_num` int(10) unsigned not null default 0 COMMENT '助教服务人数' after `type`;");
        }

        if (!$this->isFieldExist('multi_class', 'service_group_num')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `service_group_num` int(10) unsigned not null default 0 COMMENT '助教服务组数上限' after `service_num`;");
        }

        if (!$this->isFieldExist('multi_class', 'group_limit_num')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `group_limit_num` int(10) unsigned not null default 0 COMMENT '分组人数限制' after `service_num`;");
        }

        if (!$this->isFieldExist('user_profile', 'wechat_nickname')) {
            $this->getConnection()->exec("ALTER TABLE `user_profile` ADD COLUMN `wechat_nickname` varchar(512) default '' COMMENT '微信昵称' after `weixin`;");
        }

        if (!$this->isFieldExist('user_profile', 'wechat_picture')) {
            $this->getConnection()->exec("ALTER TABLE `user_profile` ADD COLUMN `wechat_picture` varchar(256) default '' COMMENT '微信头像' after `wechat_nickname`;");
        }

        if (!$this->isFieldExist('user', 'scrmStaffId')) {
            $this->getConnection()->exec("ALTER TABLE `user` ADD COLUMN `scrmStaffId` int(11) NOT NULL DEFAULT '0' COMMENT 'Scrm平台员工ID' AFTER scrmUuid;");
        }

        return 1;
    }

    public function initMultiClassJob()
    {
        $job = $this->getConnection()->fetchAssoc("select * from biz_scheduler_job where name = 'GenerateReplayJob'");
        if (empty($job)) {
            $randNum = rand(1, 59);
            $currentTime = time();
            $this->getConnection()->exec("
                INSERT INTO `biz_scheduler_job` (
                    `name`,
                    `pool`,
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
                    'GenerateReplayJob',
                    'default',
                    '{$randNum}/60 * * * *',
                    'Biz\\\\Live\\\\Job\\\\GenerateReplayJob',
                    '',
                    '100',
                    '{$currentTime}',
                    '0',
                    'executing',
                    '1',
                    '0',
                    '{$currentTime}',
                    '{$currentTime}'
                );
            ");
        }

        $job = $this->getConnection()->fetchAssoc("select * from biz_scheduler_job where name = 'UploadSCRMUserDataJob'");
        if (empty($job)) {
            $randNum = rand(1, 29);
            $currentTime = time();
            $this->getConnection()->exec("
                INSERT INTO `biz_scheduler_job` (
                    `name`,
                    `pool`,
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
                    'UploadSCRMUserDataJob',
                    'default',
                    '{$randNum}/30 * * * *',
                    'Biz\\\\SCRM\\\\Job\\\\UploadSCRMUserDataJob',
                    '',
                    '100',
                    '{$currentTime}',
                    '0',
                    'executing',
                    '1',
                    '0',
                    '{$currentTime}',
                    '{$currentTime}'
                );
            ");
        }

        return 1;
    }

    public function modifyEducationAdminRole()
    {
        $this->getConnection()->exec("
            UPDATE role SET data_v2 = '[\"admin_v2\",\"admin_v2_education\",\"admin_v2_education_overview\",\"admin_v2_education_overview_data\",\"admin_v2_education_overview_manage\",\"admin_v2_education_multi_class\",\"admin_v2_multi_class_inspection\",\"admin_v2_multi_class_inspection_manage\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_education_manage\",\"admin_v2_multi_class_product\",\"admin_v2_multi_class_product_manage\",\"admin_v2_teacher\",\"admin_v2_teacher_manage\",\"admin_v2_assistant\",\"admin_v2_assistant_manage\",\"admin_v2_multi_class_setting\",\"admin_v2_multi_class_setting_manage\",\"admin_v2_teach\",\"admin_v2_course_group\",\"admin_v2_course_show\",\"admin_v2_course_manage\",\"admin_v2_course_content_manage\",\"admin_v2_go_to_choose\",\"admin_v2_course_add\",\"admin_v2_course_set_recommend\",\"admin_v2_course_set_cancel_recommend\",\"admin_v2_course_guest_member_preview\",\"admin_v2_course_set_close\",\"admin_v2_course_sms_prepare\",\"admin_v2_course_set_clone\",\"admin_v2_course_set_publish\",\"admin_v2_course_set_delete\",\"admin_v2_course_set_remove\",\"admin_v2_course_set_recommend_list\",\"admin_v2_course_set_data\",\"admin_v2_classroom\",\"admin_v2_classroom_manage\",\"admin_v2_classroom_content_manage\",\"admin_v2_classroom_create\",\"admin_v2_classroom_cancel_recommend\",\"admin_v2_classroom_set_recommend\",\"admin_v2_classroom_close\",\"admin_v2_classroom_open\",\"admin_v2_classroom_delete\",\"admin_v2_sms_prepare\",\"admin_v2_classroom_recommend\",\"admin_v2_classroom_statistics\",\"admin_v2_live_course\",\"admin_v2_item_bank_exercise_manage\",\"admin_v2_course_category_tag\",\"admin_v2_course_category\",\"admin_v2_tag\",\"admin_v2_tool_group\",\"admin_v2_course_note\",\"admin_v2_course_question\",\"admin_v2_course_thread\",\"admin_v2_review\",\"admin_v2_cloud_resource_group\",\"admin_v2_cloud_resource\",\"admin_v2_cloud_file\",\"admin_v2_cloud_attachment\",\"admin_v2_cloud_file_setting\",\"admin_v2_cloud_attachment_setting\",\"admin_v2_question_bank\"]' WHERE code = 'ROLE_EDUCATIONAL_ADMIN';
        ");

        return 1;
    }

    public function initMultiClassTime($page)
    {
        $count = $this->getTableCount('multi_class');
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $multiClasses = $this->getConnection()->fetchAll("select id, courseId from multi_class order by id ASC limit {$start},{$this->perPageCount};");
            if (!empty($multiClasses)) {
                foreach ($multiClasses as $multiClass) {
                    $firstActivity = $this->getConnection()->fetchAssoc("select id, startTime, endTime from activity where mediaType = 'live' and fromCourseId = {$multiClass['courseId']} order by startTime ASC limit 1;");
                    $endActivity = $this->getConnection()->fetchAssoc("select id, startTime, endTime from activity where mediaType = 'live' and fromCourseId = {$multiClass['courseId']} order by endTime DESC limit 1;");
                    if (!empty($firstActivity)) {
                        $this->getConnection()->exec("update multi_class set start_time={$firstActivity['startTime']}, end_time={$endActivity['endTime']} where id = {$multiClass['id']};");
                    }
                }
            }

            $this->logger('info', "更新multi_class，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }
    }

    public function initMultiClassSetting()
    {
        $setting = $this->getSettingService()->get('multi_class', []);
        if (empty($setting)) {
            $setting = [
                'group_number_limit' => '20',
                'assistant_group_limit' => '0',
                'assistant_service_limit' => '20',
                'review_time_limit' => '0'
            ];
            $this->getSettingService()->set('multi_class', $setting);
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
