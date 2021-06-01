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
            'addMultiClassTables',
            'addDefaultProduct',
            'updateRolePermission',
            'initAssistantPermission',
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

    public function addMultiClassTables()
    {
        if (!$this->isTableExist('multi_class_product')) {
            $this->getConnection()->exec("
                CREATE TABLE `multi_class_product` (
                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                  `title` varchar(64) NOT NULL COMMENT '产品名称',
                  `type` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '产品类型',
                  `remark` varchar(64) DEFAULT '' COMMENT '备注',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课产品';"
            );
        }

        if (!$this->isTableExist('multi_class')) {
            $this->getConnection()->exec("
                CREATE TABLE `multi_class` (
                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                  `title` varchar(64) NOT NULL COMMENT '班课名称',
                  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `productId` int(10) unsigned NOT NULL COMMENT '产品ID',
                  `copyId` int(10) unsigned default 0 COMMENT '复制来源ID',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课';"
            );
        }

        if (!$this->isFieldExist('course_member', 'multiClassId')) {
            $this->getConnection()->exec("ALTER TABLE `course_member` ADD `multiClassId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '班课ID' AFTER `courseId`;");
        }

        if ($this->isFieldExist('course_member', 'role')) {
            $this->getConnection()->exec("ALTER TABLE `course_member` CHANGE `role` `role` enum('student','teacher', 'assistant') NOT NULL DEFAULT 'student' COMMENT '成员角色';");
        }

        if (!$this->isFieldExist('course_task', 'multiClassId')) {
            $this->getConnection()->exec("ALTER TABLE `course_task` ADD `multiClassId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '班课ID' AFTER `courseId`;");
        }

        return 1;
    }

    public function addDefaultProduct()
    {
        $sql = "select * from multi_class_product where type='default';";
        $result = $this->getConnection()->fetchAssoc($sql);

        if (empty($result)) {
            $currentTime = time();
            $this->getConnection()->exec("
                INSERT INTO `multi_class_product` 
                    (`title`, `type`, `remark`, `createdTime`, `updatedTime`) 
                VALUES 
                    ('默认产品', 'default', '系统默认产品包', '{$currentTime}', '{$currentTime}');
            );
        ");
        }

        return 1;
    }

    public function updateRolePermission()
    {
        $currentTime = time();
        $this->getConnection()->exec("
            INSERT INTO `role` (`name`, `code`, `data_v2`, `createdTime`, `createdUserId`, `updatedTime`) 
            VALUES ('助教','ROLE_TEACHER_ASSISTANT','[\"admin_v2\",\"admin_v2_teach\",\"admin_v2_course_group\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"web\",\"course_manage\",\"course_manage_info\",\"course_manage_base\",\"course_manage_detail\",\"course_manage_picture\",\"course_manage_lesson\",\"live_course_manage_replay\",\"course_manage_files\",\"course_manage_setting\",\"course_manage_price\",\"course_manage_teachers\",\"course_manage_students\",\"course_manage_student_create\",\"course_manage_questions\",\"course_manage_question\",\"course_manage_testpaper\",\"course_manange_operate\",\"course_manage_data\",\"course_manage_order\",\"classroom_manage\",\"classroom_manage_settings\",\"classroom_manage_set_info\",\"classroom_manage_set_price\",\"classroom_manage_set_picture\",\"classroom_manage_service\",\"classroom_manage_headteacher\",\"classroom_manage_teachers\",\"classroom_manage_assistants\",\"classroom_manage_content\",\"classroom_manage_courses\",\"classroom_manage_students\",\"classroom_manage_testpaper\"]','{$currentTime}',1,'{$currentTime}');
        ");

        $this->getConnection()->exec("
            UPDATE role SET data_v2 = '[\"admin_v2\",\"admin_v2_teach\",\"admin_v2_course_group\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_course_show\",\"admin_v2_course_manage\",\"admin_v2_course_content_manage\",\"admin_v2_course_add\",\"admin_v2_course_guest_member_preview\",\"admin_v2_course_set_close\",\"admin_v2_course_set_clone\",\"admin_v2_course_set_publish\",\"admin_v2_course_set_delete\",\"admin_v2_course_set_remove\",\"admin_v2_course_set_data\",\"web\",\"course_manage\",\"course_manage_info\",\"course_manage_base\",\"course_manage_detail\",\"course_manage_picture\",\"course_manage_lesson\",\"live_course_manage_replay\",\"course_manage_files\",\"course_manage_setting\",\"course_manage_price\",\"course_manage_teachers\",\"course_manage_students\",\"course_manage_student_create\",\"course_manage_questions\",\"course_manage_question\",\"course_manage_testpaper\",\"course_manange_operate\",\"course_manage_data\",\"course_manage_order\",\"classroom_manage\",\"classroom_manage_settings\",\"classroom_manage_set_info\",\"classroom_manage_set_price\",\"classroom_manage_set_picture\",\"classroom_manage_service\",\"classroom_manage_headteacher\",\"classroom_manage_teachers\",\"classroom_manage_assistants\",\"classroom_manage_content\",\"classroom_manage_courses\",\"classroom_manage_students\",\"classroom_manage_testpaper\"]' WHERE code = 'ROLE_TEACHER';
        ");

        return 1;
    }

    public function initAssistantPermission()
    {
        $setting = $this->getSettingService()->get('assistant_permission', []);
        if (!empty($setting)) {
            return 1;
        }

        $permissions = [
            'multi_class_manage',
            'course_manage',
            'course_lesson_manage',
            'course_member_manage',
            'course_member_create',
            'course_member_deadline_edit',
            'course_member_import',
            'course_live_manage',
            'course_homework_review',
            'course_exam_review',
            'course_statistics_view',
            'course_announcement_manage',
            'course_replay_manage',
            'course_question_marker_manage',
            'course_order_manage',
        ];
        $this->getSettingService()->set('assistant_permission', $permissions);

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
