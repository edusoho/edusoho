<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Service\ActivityLearnLogService;
use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\Framework\Context\Biz;
use Monolog\Logger;

class Activity
{
    private $biz;

    final public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * 实现Activity附属信息的同步.
     *
     * @param array $sourceActivity 源activity
     * @param array $activity       目标activity
     *
     * @return mixed
     */
    public function sync($sourceActivity, $activity)
    {
        return null;
    }

    public function updateToLastedVersion($sourceActivity, $activity)
    {
        return null;
    }

    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    protected function setting($name, $default = null)
    {
        return $this->getSettingService()->node($name, $default);
    }

    /**
     * @return Biz
     */
    final protected function getBiz()
    {
        return $this->biz;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:ActivityLearnLogService');
    }

    protected function createDao($daoAlias)
    {
        return $this->getBiz()->dao($daoAlias);
    }

    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getBiz()->offsetGet('s2b2c.merchant.logger');
    }
}
