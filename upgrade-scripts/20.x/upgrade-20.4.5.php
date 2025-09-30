<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

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
            'updateBizSchedulerJob',
            'updateGoodsSpecsNums',
            'updateGoodsSpecsTitle',
            'updateOpenCourseRecommendGoodsId',
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

    public function updateBizSchedulerJob()
    {
        $expression = rand(0, 15).'/15 * * * *';

        if ($this->isTableExist('biz_scheduler_job')) {
            $this->getConnection()->exec("
                UPDATE `biz_scheduler_job` SET expression = '{$expression}' WHERE name = 'CheckConvertStatusJob';
            ");
        }
        
        $this->logger('info', '更新同步云资源定时任务');
        return 1;
    }


    public function updateGoodsSpecsNums()
    {
        if ($this->isTableExist('goods') && $this->isTableExist('goods_specs')) {
            $this->getConnection()->exec("
                UPDATE goods g INNER JOIN (
                    SELECT g.id, COUNT(gs.id) AS num FROM goods g, goods_specs gs 
                        WHERE g.id = gs.goodsId GROUP BY g.id
                ) m SET g.specsNum = m.num WHERE g.id = m.id;
            ");
            $this->logger('info', '更新goods表的specsNum成功.');

            $this->getConnection()->exec("
                UPDATE goods g INNER JOIN (
                    SELECT g.id, COUNT(gs.id) AS num FROM goods g, goods_specs gs 
                        WHERE g.id = gs.goodsId AND gs.status = 'published' GROUP BY g.id
                ) m SET g.publishedSpecsNum = m.num WHERE g.id = m.id;
            ");

            $this->logger('info', '更新goods表的publishedSpecsNum成功.');
        }
        return 1;
    }

    public function updateGoodsSpecsTitle()
    {
        if ($this->isTableExist('goods') && $this->isTableExist('goods_specs')) {
            $this->getConnection()->exec("
                UPDATE `goods_specs` SET title = '' WHERE id IN ( 
                    SELECT id FROM (
                        SELECT gs.id AS id FROM goods g, goods_specs gs WHERE g.id = gs.goodsId AND g.type = 'course' AND gs.title = g.title AND g.specsNum = 1
                    ) gds
                );
            ");
        }
        $this->logger('info', '更新goods_specs表的title成功.');
        return 1;
    }

    public function updateOpenCourseRecommendGoodsId()
    {
        if ($this->isTableExist('open_course_recommend') && $this->isTableExist('course_v8') && $this->isTableExist('product') && $this->isTableExist('goods') ) {
            $this->getConnection()->exec("
                UPDATE `open_course_recommend` oc INNER  JOIN (
                    SELECT o.id as id, g.id as goodsId FROM `open_course_recommend` o 
                        JOIN `course_v8` c ON c.id = o.`recommendCourseId` AND o.recommendGoodsId = 0 
                        JOIN `product` p ON p.targetId = c.courseSetId AND p.targetType='course' 
                        JOIN `goods` g ON g.productId = p.id) m 
                    ON m.id = oc.id SET oc.recommendGoodsId = m.goodsId;
            ");
        }

        $this->logger('info', '更新open_course_recommend表的recommendGoodsId成功.');
        return 1;
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
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
