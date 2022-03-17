<?php

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Dao\HomeworkActivityDao;
use Biz\Task\Dao\TaskDao;
use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
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
            'addTaskDisplayAndChangeIdcard',
            'processActivityFinishData',
            'updateAnswerReport',
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

    public function addTaskDisplayAndChangeIdcard()
    {
        if (!$this->isFieldExist('course_v8', 'taskDisplay')) {
            $this->getConnection()->exec("ALTER TABLE `course_v8` ADD COLUMN `taskDisplay` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '目录展示';");
        }
        if ($this->isFieldExist('user_approval', 'idcard')) {
            $this->getConnection()->exec("ALTER TABLE `user_approval` MODIFY `idcard` VARCHAR(51);");
        }
        if ($this->isFieldExist('user_profile', 'idcard')) {
            $this->getConnection()->exec("ALTER TABLE `user_profile` MODIFY `idcard` VARCHAR(51);");
        }
        $this->getConnection()->exec("DELETE from  `setting` where name = 'siteTrace' limit 1;");
        $this->getConnection()->exec("DELETE from  `cache` where name = 'settings' limit 1;");

        return 1;
    }

    public function processActivityFinishData()
    {
        $cloudAppLog = $this->getAppLogDao()->getLastLogByCodeAndToVersion('MAIN', '21.4.10');
        if (!empty($cloudAppLog)) {
            $activities = $this->getActivityDao()->findActivitiesByTypeAndCreatedTimeAndUpdatedTimeFinishType('video', $cloudAppLog['createdTime'], $cloudAppLog['createdTime'], 'end');
            $activityIds = ArrayToolkit::column($activities, 'id');
            if (!empty($activityIds)) {
                $this->getActivityDao()->update($activityIds, ['finishData' => 0, 'updatedTime' => time()]);
            }
        }

        return 1;
    }

    public function updateAnswerReport($page)
    {
        $cloudAppLog = $this->getAppLogDao()->getLastLogByCodeAndToVersion('MAIN', '22.1.3');
        if (empty($cloudAppLog)) {
            return 1;
        }
        $answerReports = $this->getAnswerReportService()->search(['created_time_LE' => $cloudAppLog['createdTime'], 'excludeGrades' => ['passed']], [], ($page - 1) * 5000, 5000, ['id', 'answer_scene_id', 'score']);
        if (empty($answerReports)) {
            return 1;
        }
        $answerSceneIds = ArrayToolkit::column($answerReports, 'answer_scene_id');
        $answerReportGroups = ArrayToolkit::group($answerReports, 'answer_scene_id');
        $answerScenes = $this->getAnswerSceneService()->search(['ids' => $answerSceneIds], [], 0, count($answerSceneIds), ['id', 'pass_score']);
        $update = [];
        foreach ($answerScenes as $answerScene) {
            if (!isset($answerReportGroups[$answerScene['id']])) {
                continue;
            }
            foreach ($answerReportGroups[$answerScene['id']] as $report) {
                if ($report['score'] >= $answerScene['pass_score']) {
                    $update[$report['id']] = 'passed';
                } else {
                    $update[$report['id']] = 'unpassed';
                }
            }
        }
        if ($update) {
            $this->getAnswerReportService()->batchUpdate(array_keys($update), $update);
        }
        unset($answerReports, $answerSceneIds, $answerReportGroups, $answerScenes, $update);
        $this->logger('info', '更新答题结果answer_report'.$page);

        return $page + 1;
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
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

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
