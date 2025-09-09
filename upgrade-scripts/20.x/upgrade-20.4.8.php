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
            'processGoodsHitNum',
            'processCourseTaskNum',
            'processCourseCompulsoryTaskNum',
            'processCourseElectiveTaskNum',
            'addTableIndexJob',
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

    public function processGoodsHitNum()
    {
        if (empty($this->getCacheService()->get('goods_hit_num_is_process'))) {
            $this->logger('info', '开始处理：GoodsHitNum');

            $this->getConnection()->exec("
                UPDATE course_set_v8 a
                    INNER JOIN (
                        SELECT courseSetId, SUM(hitNum) AS sumHitNum
                        FROM course_v8
                        GROUP BY courseSetId
                    ) b
                    ON a.id = b.courseSetId
                SET a.hitNum = a.hitNum + b.sumHitNum;
            ");

            $this->getConnection()->exec("
                UPDATE goods
                    INNER JOIN product
                    ON product.id = goods.productId AND product.targetType = 'course'
                    INNER JOIN course_set_v8 ON course_set_v8.id = product.targetId
                SET goods.hitNum = course_set_v8.hitNum;
            ");
            
            $this->getCacheService()->set('goods_hit_num_is_process', 1);
        }
      
        return 1;
    }

    public function processCourseTaskNum()
    {
        $this->logger('info', '开始处理：CourseTaskNum');

        $this->getConnection()->exec("
            UPDATE course_v8 a
                INNER JOIN (
                    SELECT courseId, COUNT(*) AS taskNum2
                    FROM course_task
                    GROUP BY courseId
                ) b
                ON a.id = b.courseId
            SET a.taskNum = b.taskNum2;
        ");

        return 1;
    }

    public function processCourseCompulsoryTaskNum()
    {
        $this->logger('info', '开始处理：CourseCompulsoryTaskNum');
        
        $this->getConnection()->exec("
            UPDATE course_v8 a
                INNER JOIN (
                    SELECT courseId, COUNT(*) AS compulsoryTaskNum2
                    FROM course_task
                    WHERE isOptional = 0
                    GROUP BY courseId
                ) b
                ON a.id = b.courseId
            SET a.compulsoryTaskNum = b.compulsoryTaskNum2;
        ");

        return 1;
    }

    public function processCourseElectiveTaskNum()
    {
        $this->logger('info', '开始处理：CourseElectiveTaskNum');

        if (!$this->isFieldExist('course_v8', 'electiveTaskNum')) {
            $this->getConnection()->exec("
                ALTER TABLE `course_v8` ADD COLUMN `electiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '选修任务数' AFTER `compulsoryTaskNum`;
            ");
        }

        $this->getConnection()->exec("
            UPDATE course_v8 a
                INNER JOIN (
                    SELECT courseId, COUNT(*) AS electiveTaskNum2
                    FROM course_task
                    WHERE isOptional = 1
                    GROUP BY courseId
                ) b
                ON a.id = b.courseId
            SET a.electiveTaskNum = b.electiveTaskNum2;
        ");

        return 1;
    }

    public function addTableIndexJob()
    {
        if ($this->getConnection()->fetchColumn("SELECT COUNT(*) FROM `course_member`") < 100000) {
            if (!$this->isFieldExist('course_member', 'learnedElectiveTaskNum')) {
                $this->getConnection()->exec("
                    ALTER TABLE `course_member` ADD COLUMN `learnedElectiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学习的选修任务数量' AFTER `learnedCompulsoryTaskNum`;
                ");
            }
            return 1;
        }

        if ($this->isJobExist('HandlingTimeConsumingUpdateStructuresJob')) {
            return 1;
        }

        $currentTime = time();
        $time = time() + 60;

        $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
              `name`,
              `expression`,
              `class`,
              `args`,
              `priority`,
              `pre_fire_time`,
              `next_fire_time`,
              `misfire_threshold`,
              `misfire_policy`,
              `enabled`,
              `creator_id`,
              `updated_time`,
              `created_time`
        ) VALUES (
              'HandlingTimeConsumingUpdateStructuresJob',
              '',
              'Biz\\\\UpdateDatabaseStructure\\\\\Job\\\\HandlingTimeConsumingUpdateStructuresJob',
              '',
              '200',
              '0',
              '{$time}',
              '300',
              'executing',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
        $this->logger('info', 'INSERT增加索引的定时任务HandlingTimeConsumingUpdateStructuresJob');
        return 1;
    }

    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
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
