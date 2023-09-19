<?php

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
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
            'processingWithHomeworkNeedScore',
            'processingWithLiveActivityCloum',
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

    public function processingWithHomeworkNeedScore()
    {

        $conditions = [];
        $conditions['mediaType'] = 'homework';
        $conditions['finishType'] = 'score';
        $activityList = $this->getActivityService()->search($conditions, [], 0, PHP_INT_MAX, ["mediaId"]);
        if (empty($activityList)) {
            return 1;
        }

        $mediaIds = array_column($activityList, 'mediaId');

        $homeworkActivityList = $this->getHomeworkActivityService()->findByIds($mediaIds);
        if (empty($homeworkActivityList)) {
            return 1;
        }

        $answerSceneIds = array_column($homeworkActivityList, 'answerSceneId');
        $answerSceneList = $this->getAnswerSceneService()->search(['need_score' => 0, 'ids' => array_unique($answerSceneIds)], [], 0, PHP_INT_MAX, ['id']);
        if (empty($answerSceneList)) {
            return 1;
        }

        foreach ($answerSceneList as $answerScene) {
            $this->getAnswerSceneDao()->update($answerScene['id'], ['need_score' => 1]);
        }

        return 1;
    }

    public function processingWithLiveActivityCloum()
    {

        $this->getConnection()->exec("ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus`  enum('ungenerated','generating','generated','failure','videoGenerated') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态';");

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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {

        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {

        return $this->createService('Activity:HomeworkActivityService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {

        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAnswerSceneDao()
    {
        return $this->createDao('ItemBank:Answer:AnswerSceneDao');
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
