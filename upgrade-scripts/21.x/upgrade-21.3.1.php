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
            'addTable',
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

    public function addTable()
    {
        if (!$this->isTableExist('biz_wrong_book_exercise')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_wrong_book_exercise` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `answer_scene_id` int(11) unsigned NOT NULL COMMENT '场次ID',
                  `assessment_id` int(11) unsigned NOT NULL COMMENT '试卷ID',
                  `regulation` text COMMENT '做题规则',
                  `user_id` int(11) unsigned NOT NULL COMMENT '做题人',
                  `created_time` int(11) unsigned NOT NULL DEFAULT '0',
                  `updated_time` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
            $this->logger('info', '新增biz_wrong_book_exercise');
        }
        if (!$this->isTableExist('biz_wrong_question')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_wrong_question` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `collect_id` int(11) unsigned NOT NULL COMMENT '题目集合ID',
                  `user_id` int(11) unsigned NOT NULL COMMENT '错题用户ID',
                  `item_id` int(11) unsigned NOT NULL COMMENT 'biz_item ID ',
                  `question_id` int(11) unsigned NOT NULL COMMENT 'biz_question ID ',
                  `answer_scene_id` int(11) unsigned NOT NULL COMMENT '场次ID',
                  `testpaper_id` int(11) unsigned NOT NULL COMMENT '考试试卷ID',
                  `answer_question_report_id` int(11) unsigned NOT NULL COMMENT '题目报告ID',
                  `submit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '提交时间',
                  `source_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
                  `source_type` varchar(32) DEFAULT '' COMMENT '来源类型',
                  `created_time` int(11) unsigned NOT NULL DEFAULT '0',
                  `updated_time` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='错题记录表';
            ");
            $this->logger('info', '新增biz_wrong_question');
        }
        if (!$this->isTableExist('biz_wrong_question_book_pool')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_wrong_question_book_pool` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) unsigned NOT NULL COMMENT '错题用户ID',
                  `item_num` int(11) unsigned NOT NULL COMMENT '错题数量',
                  `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '所属类型',
                  `target_id` int(11) unsigned NOT NULL COMMENT '所属ID',
                  `scene_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '错题练习sceneId',
                  `created_time` int(11) NOT NULL DEFAULT '0',
                  `updated_time` int(11) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='错题池';
            ");
            $this->logger('info', '新增biz_wrong_question_book_pool');
        }
        if (!$this->isTableExist('biz_wrong_question_collect')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_wrong_question_collect` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `pool_id` int(11) unsigned NOT NULL COMMENT '池子ID',
                  `item_id` int(11) unsigned NOT NULL COMMENT 'biz_item ID',
                  `status` varchar(32) DEFAULT 'wrong' COMMENT '题目状态： wrong，correct',
                  `wrong_times` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '错误频次',
                  `last_submit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后错题时间',
                  `created_time` int(11) unsigned NOT NULL DEFAULT '0',
                  `updated_time` int(11) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='错题统计集合表';
            ");
            $this->logger('info', '新增biz_wrong_question_collect');
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
