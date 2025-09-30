<?php

use Biz\Search\Constant\CloudSearchType;
use Symfony\Component\Filesystem\Filesystem;

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
        $definedFuncNames = [
            'enableItemBankExerciseCloudSearch',
            'createTableBizAnswerReviewedQuestion',
            'createTableBizAssessmentSnapshot',
            'alterTableBizItemCategoryAndItemBankExercise',
            'enableQuestionSoftDelete',
            'alterTableBizQuestionFavorite',
            'alterTableActivityHomework',
            'alterTableBizAnswerRecord',
        ];
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

    public function enableItemBankExerciseCloudSearch()
    {
        $searchSetting = $this->getSettingService()->get('cloud_search');
        if (empty($searchSetting['type'])) {
            return 1;
        }

        $searchSetting['type'] = [
            CloudSearchType::COURSE => $searchSetting['type']['course'] ?? 1,
            CloudSearchType::CLASSROOM => $searchSetting['type']['classroom'] ?? 1,
            CloudSearchType::ITEM_BANK_EXERCISE => 1,
            CloudSearchType::TEACHER => $searchSetting['type']['teacher'] ?? 1,
            CloudSearchType::THREAD => $searchSetting['type']['thread'] ?? 1,
            CloudSearchType::ARTICLE => $searchSetting['type']['article'] ?? 1,
        ];

        $this->getSettingService()->set('cloud_search', $searchSetting);

        return 1;
    }

    public function createTableBizAnswerReviewedQuestion()
    {
        $this->getConnection()->exec(
            "CREATE TABLE IF NOT EXISTS `biz_answer_reviewed_question` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `answer_record_id` INT(10) unsigned NOT NULL COMMENT '答题记录id',
              `question_id` int(10) unsigned NOT NULL COMMENT '问题id',
              `is_reviewed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否批阅 0未批 1已批',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `answer_record_id` (`answer_record_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        return 1;
    }

    public function createTableBizAssessmentSnapshot()
    {
        $this->getConnection()->exec(
            "CREATE TABLE IF NOT EXISTS `biz_assessment_snapshot` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `origin_assessment_id` INT(10) unsigned NOT NULL COMMENT '原试卷id',
              `snapshot_assessment_id` INT(10) unsigned NOT NULL COMMENT '快照试卷id',
              `sections_snapshot` text COMMENT '原section和快照section对应关系',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `origin_assessment_id` (`origin_assessment_id`),
              KEY `snapshot_assessment_id` (`snapshot_assessment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        return 1;
    }

    public function alterTableBizItemCategoryAndItemBankExercise()
    {
        if (!$this->isFieldExist('biz_item_category', 'seq')) {
            $this->getConnection()->exec("ALTER TABLE `biz_item_category` ADD COLUMN `seq` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '同一父分类下分类排序' AFTER `weight`;");
        }
        if (!$this->isFieldExist('item_bank_exercise', 'hiddenChapterIds')) {
            $this->getConnection()->exec("ALTER TABLE `item_bank_exercise` ADD COLUMN `hiddenChapterIds` TEXT COMMENT '不发布的章节(题目分类)id序列' AFTER `teacherIds`;");
        }

        return 1;
    }

    public function enableQuestionSoftDelete()
    {
        if (!$this->isFieldExist('biz_item', 'is_deleted')) {
            $this->getConnection()->exec("ALTER TABLE `biz_item` ADD COLUMN `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除';");
        }
        if (!$this->isFieldExist('biz_item', 'deleted_time')) {
            $this->getConnection()->exec("ALTER TABLE `biz_item` ADD COLUMN `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间';");
        }
        if (!$this->isFieldExist('biz_item_attachment', 'is_deleted')) {
            $this->getConnection()->exec("ALTER TABLE `biz_item_attachment` ADD COLUMN `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除';");
        }
        if (!$this->isFieldExist('biz_item_attachment', 'deleted_time')) {
            $this->getConnection()->exec("ALTER TABLE `biz_item_attachment` ADD COLUMN `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间';");
        }
        if (!$this->isFieldExist('biz_question', 'is_deleted')) {
            $this->getConnection()->exec("ALTER TABLE `biz_question` ADD COLUMN `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除';");
        }
        if (!$this->isFieldExist('biz_question', 'deleted_time')) {
            $this->getConnection()->exec("ALTER TABLE `biz_question` ADD COLUMN `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间';");
        }

        return 1;
    }

    public function alterTableBizQuestionFavorite()
    {
        if (!$this->isIndexExist('biz_question_favorite', 'item_id')) {
            $this->getConnection()->exec('ALTER TABLE `biz_question_favorite` ADD INDEX `item_id` (`item_id`);');
        }

        return 1;
    }

    public function alterTableActivityHomework()
    {
        if (!$this->isFieldExist('activity_homework', 'assessmentBankId')) {
            $this->getConnection()->exec("ALTER TABLE `activity_homework` ADD COLUMN `assessmentBankId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '试卷所属题库id' AFTER `assessmentId`;");
        }
        $this->getSchedulerService()->register([
            'name' => 'HomeworkActivitySetAssessmentBankIdJob',
            'expression' => (time() + 60),
            'class' => 'Biz\Activity\Job\HomeworkActivitySetAssessmentBankIdJob',
            'misfire_threshold' => 60 * 60,
            'misfire_policy' => 'executing',
        ]);

        return 1;
    }

    public function alterTableBizAnswerRecord()
    {
        if (!$this->isFieldExist('biz_answer_record', 'exercise_mode')) {
            $this->getConnection()->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `exercise_mode` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '练习模式 0测试模式 1一题一答' AFTER `exam_mode`;");
        }

        return 1;
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

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getJobLogDao()
    {
        return $this->createDao('Scheduler:JobLogDao');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
