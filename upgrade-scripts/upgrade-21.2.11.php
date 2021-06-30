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
            'alterMultiClass',
            'addAssistantStudent',
            'initRole',
            'addWeChatQrCode',
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

    public function alterMultiClass()
    {
        if (!$this->isFieldExist('multi_class', 'maxStudentNum')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `maxStudentNum` int(11) NOT NULL DEFAULT '0' COMMENT '限购人数' AFTER productId;");
        }

        if (!$this->isFieldExist('multi_class', 'isReplayShow')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `isReplayShow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '回放是否显示' AFTER maxStudentNum;");
        }

        if (!$this->isFieldExist('multi_class', 'liveRemindTime')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `liveRemindTime` int(11) NOT NULL DEFAULT '0' COMMENT '直播提醒时间（分钟）' AFTER isReplayShow;");
        }

        if (!$this->isFieldExist('multi_class', 'creator')) {
            $this->getConnection()->exec("ALTER TABLE `multi_class` ADD COLUMN `creator` int(11) NOT NULL DEFAULT '0' COMMENT '创建者' AFTER copyId;");
        }

        return 1;
    }

    public function addAssistantStudent()
    {
        if (!$this->isTableExist('assistant_student')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `assistant_student` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `assistantId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '助教ID',
                  `studentId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '学员ID',
                  `courseId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '课程ID',
                  `multiClassId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '班课ID',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT 0,
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT 0,
                  PRIMARY KEY (`id`),
                  key `course_assistant` (`assistantId`, `courseId`),
                  key `multiClass_student` (`multiClassId`, `studentId`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='助教学员关系表';"
            );

            $this->logger('info', '新增assistant_student表');
        }
        
        return 1;
    }

    public function initRole()
    {
        $sql = "select * from role where code='ROLE_EDUCATIONAL_ADMIN';";
        $result = $this->getConnection()->fetchAssoc($sql);

        if (empty($result)) {
            $currentTime = time();
            $this->getConnection()->exec("
                INSERT INTO `role` (`name`, `code`, `data_v2`, `createdTime`, `createdUserId`, `updatedTime`) VALUES ('教务', 'ROLE_EDUCATIONAL_ADMIN', '[\"admin_v2\",\"admin_v2_education\",\"admin_v2_education_manage\",\"admin_v2_multi_class_product\",\"admin_v2_multi_class_product_manage\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_teacher\",\"admin_v2_teacher_manage\",\"admin_v2_assistant\",\"admin_v2_assistant_manage\",\"admin_v2_teach\",\"admin_v2_course_group\",\"admin_v2_course_show\",\"admin_v2_course_manage\",\"admin_v2_course_content_manage\",\"admin_v2_go_to_choose\",\"admin_v2_course_add\",\"admin_v2_course_set_recommend\",\"admin_v2_course_set_cancel_recommend\",\"admin_v2_course_guest_member_preview\",\"admin_v2_course_set_close\",\"admin_v2_course_sms_prepare\",\"admin_v2_course_set_clone\",\"admin_v2_course_set_publish\",\"admin_v2_course_set_delete\",\"admin_v2_course_set_remove\",\"admin_v2_course_set_recommend_list\",\"admin_v2_course_set_data\",\"admin_v2_classroom\",\"admin_v2_classroom_manage\",\"admin_v2_classroom_content_manage\",\"admin_v2_classroom_create\",\"admin_v2_classroom_cancel_recommend\",\"admin_v2_classroom_set_recommend\",\"admin_v2_classroom_close\",\"admin_v2_classroom_open\",\"admin_v2_classroom_delete\",\"admin_v2_sms_prepare\",\"admin_v2_classroom_recommend\",\"admin_v2_classroom_statistics\",\"admin_v2_live_course\",\"admin_v2_item_bank_exercise_manage\",\"admin_v2_course_category_tag\",\"admin_v2_course_category\",\"admin_v2_tag\",\"admin_v2_tool_group\",\"admin_v2_course_note\",\"admin_v2_course_question\",\"admin_v2_course_thread\",\"admin_v2_review\",\"admin_v2_cloud_resource_group\",\"admin_v2_cloud_resource\",\"admin_v2_cloud_file\",\"admin_v2_cloud_attachment\",\"admin_v2_cloud_file_setting\",\"admin_v2_cloud_attachment_setting\",\"admin_v2_question_bank\",\"web\",\"course_manage\",\"course_manage_info\",\"course_manage_base\",\"course_manage_detail\",\"course_manage_picture\",\"course_manage_lesson\",\"live_course_manage_replay\",\"course_manage_files\",\"course_manage_setting\",\"course_manage_price\",\"course_manage_teachers\",\"course_manage_students\",\"course_manage_student_create\",\"course_manage_questions\",\"course_manage_question\",\"course_manage_testpaper\",\"course_manange_operate\",\"course_manage_data\",\"course_manage_order\",\"classroom_manage\",\"classroom_manage_settings\",\"classroom_manage_set_info\",\"classroom_manage_set_price\",\"classroom_manage_set_picture\",\"classroom_manage_service\",\"classroom_manage_headteacher\",\"classroom_manage_teachers\",\"classroom_manage_assistants\",\"classroom_manage_content\",\"classroom_manage_courses\",\"classroom_manage_students\",\"classroom_manage_testpaper\"]', '{$currentTime}', 1, '{$currentTime}');
            ");
        }

        $this->getConnection()->exec("
            UPDATE role SET data_v2 = '[\"web\",\"course_manage\",\"course_manage_info\",\"course_manage_base\",\"course_manage_detail\",\"course_manage_picture\",\"course_manage_lesson\",\"live_course_manage_replay\",\"course_manage_files\",\"course_manage_setting\",\"course_manage_price\",\"course_manage_teachers\",\"course_manage_students\",\"course_manage_student_create\",\"course_manage_questions\",\"course_manage_question\",\"course_manage_testpaper\",\"course_manange_operate\",\"course_manage_data\",\"course_manage_order\",\"classroom_manage\",\"classroom_manage_settings\",\"classroom_manage_set_info\",\"classroom_manage_set_price\",\"classroom_manage_set_picture\",\"classroom_manage_service\",\"classroom_manage_headteacher\",\"classroom_manage_teachers\",\"classroom_manage_assistants\",\"classroom_manage_content\",\"classroom_manage_courses\",\"classroom_manage_students\",\"classroom_manage_testpaper\"]' WHERE code in ('ROLE_TEACHER', 'ROLE_TEACHER_ASSISTANT');
        ");

        return 1;
    }

    public function addWeChatQrCode()
    {
        $this->getConnection()->exec("
            ALTER TABLE `user` ADD COLUMN `weChatQrCode` varchar(255) NOT NULL DEFAULT '' COMMENT '助教微信二维码' AFTER `largeAvatar`;
        ");

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
