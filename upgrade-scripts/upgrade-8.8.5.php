<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;

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
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
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
            'changePriceColumn',
            'changeClassroomTeacherIdsSize',
            'processItemBankAssessmentExercise',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

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

    public function processItemBankAssessmentExercise()
    {
        $this->logger('info', '开始删除刷题试卷练习任务关联的试卷已删除的记录');

        $this->getConnection()->exec("
            DELETE FROM item_bank_assessment_exercise
            WHERE id IN (
                SELECT *
                FROM (
                    SELECT a.id
                    FROM item_bank_assessment_exercise a
                        LEFT JOIN biz_assessment b ON a.assessmentId = b.id
                    WHERE b.id IS NULL
                ) c
            );
        ");

        return 1;
    }

    public function changePriceColumn()
    {
        $this->getConnection()->exec("
            ALTER TABLE `course_v8` 
            modify `price` decimal(12,2) DEFAULT '0.00' COMMENT '课程的价格',
            modify `income` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总收入',
            modify `originPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价',
            modify `coinPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币价格',
            modify `originCoinPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价';
        ");

        $this->getConnection()->exec("
            ALTER TABLE `course_set_v8`
            modify `maxCoursePrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最高价格',
            modify `minCoursePrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格';
        ");

        $this->getConnection()->exec("
            ALTER TABLE `classroom`
            modify `price` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
            modify `income` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '收入';
        ");

        $this->getConnection()->exec("
            ALTER TABLE `item_bank_exercise`
            modify `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
            modify `originPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
            modify `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '总收入';
        ");

        return 1;
    }

    public function changeClassroomTeacherIdsSize()
    {
        $this->getConnection()->exec("
            ALTER TABLE `classroom` CHANGE `teacherIds` `teacherIds` VARCHAR(4096) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '教师IDs';
        ");

        return 1;
    }

    protected function getSettingService()
    {
        return new \Biz\System\Service\Impl\SettingServiceImpl($this->biz);
    }

    private function getCacheService()
    {
        return $this->biz->service('System:CacheService');
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
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
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
