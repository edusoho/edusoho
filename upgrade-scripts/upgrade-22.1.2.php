<?php

use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Symfony\Component\Filesystem\Filesystem;

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
            $result = $this->updateScheme((int)$index);

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
            'processTable',
            'processAssessmentSectionItem',
            'processAssessmentQuestion',
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

    public function processTable()
    {
        if(!$this->isFieldExist('biz_question', 'score_rule')){
            $this->getConnection()->exec("ALTER TABLE `biz_question` ADD COLUMN `score_rule` text COMMENT '得分规则';");
        }

        if(!$this->isFieldExist('activity_testpaper', 'customComments')){
            $this->getConnection()->exec("ALTER TABLE `activity_testpaper` ADD COLUMN `customComments` text COMMENT '自动评语';");
        }
        return 1;
    }

    public function processAssessmentSectionItem($page)
    {
        $sectionItems = $this->getAssessmentSectionItemDao()->search([],['created_time'=>'ASC'],($page-1) * 500, 500);
        if(empty($sectionItems)){
            return 1;
        }
        $update = [];
        foreach ($sectionItems as $sectionItem){
            $rules = $sectionItem['score_rule'];
            foreach ($rules as &$scoreRule)
            {
                if(empty($scoreRule['rule'])){
                    continue;
                }
                $questionRules = \AppBundle\Common\ArrayToolkit::index($scoreRule['rule'], 'name');
                $allRight = $questionRules['all_right'];
                if(!empty($questionRules['part_right'])){
                    $questionRules['part_right'] = [
                        'name'=> 'part_right',
                         'score' => $allRight['score'],
                         'score_rule' => [
                             'score' => $allRight['score'],
                             'scoreType' => 'question',
                             'otherScore' => $questionRules['part_right']['score'],
                         ]
                    ];
                }else{
                    $questionRules['part_right'] = [
                        'name'=> 'part_right',
                        'score' => $allRight['score'],
                        'score_rule' => [
                            'score' => $allRight['score'],
                            'scoreType' => 'question',
                            'otherScore' => $allRight['score'],
                        ]
                    ];
                }
                $scoreRule['rule'] = array_values($questionRules);
            }
            $update[$sectionItem['id']] = ['score_rule' => $rules];
        }

        if(!empty($update)){
            $this->getAssessmentSectionItemDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        $this->logger('info', '修改AssessmentSectionItem');
        return $page+1;
    }

    public function processAssessmentQuestion($page)
    {
        $sectionQuestions = $this->getQuestionDao()->search([],['created_time'=>'ASC'],($page-1) * 500, 500);
        if(empty($sectionQuestions)){
            return 1;
        }
        $update = [];
        foreach ($sectionQuestions as $sectionQuestion){
            $update[$sectionQuestion['id']] = [
                'score_rule' => [
                'score' => $sectionQuestion['score'],
                'scoreType' => 'question',
                'otherScore' => $sectionQuestion['answer_mode'] == 'text' ?$sectionQuestion['score'] : 0,
            ]];
        }
        if(!empty($update)){
            $this->getQuestionDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        $this->logger('info', '修改biz_question');

        return $page+1;
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->createDao('ItemBank:Item:QuestionDao');
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->createDao('ItemBank:Item:ItemDao');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao
     */
    protected function getAssessmentSectionItemDao()
    {
        return $this->createDao('ItemBank:Assessment:AssessmentSectionItemDao');
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
