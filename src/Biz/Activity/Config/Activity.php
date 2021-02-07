<?php

namespace Biz\Activity\Config;

use AppBundle\Common\Exception\UnexpectedValueException;
use Biz\Activity\Listener\Listener;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class Activity
{
    private $biz;

    /**
     * 是否支持资料
     * 在创建时，会根据此方法判断，查找material，更改此资源的被使用情况
     *
     * @return bool
     */
    public function materialSupported()
    {
        return false;
    }

    public function preCreateCheck($fields)
    {
    }

    public function preUpdateCheck($activity, $newFields)
    {
    }

    public function create($fields)
    {
    }

    /**
     * @param int   $targetId
     * @param array $fields   fields to update
     * @param array $activity existed activity
     */
    public function update($targetId, &$fields, $activity)
    {
    }

    public function delete($targetId)
    {
    }

    /**
     * 实现Activity的复制，这里仅需要处理Activity的附属信息.
     *
     * @param array $activity 要复制的Activity
     * @param array $config   : newLiveroom => true/false // 是否新建直播教室（对于直播任务）
     *
     * @return mixed
     */
<<<<<<< HEAD
    public function copy($activity, $config = array())
=======
    public function copy($activity, $config = [])
>>>>>>> 3cb8cbcd7bb55ffd28294506a7bcc2f79c90008f
    {
        return null;
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

    public function allowTaskAutoStart($activity)
    {
        return true;
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        if ('time' === $activity['finishType']) {
            $result = $this->getTaskResultService()->getMyLearnedTimeByActivityId($activityId);
            $result /= 60;

            return !empty($result) && $result >= $activity['finishData'];
<<<<<<< HEAD
=======
        } elseif ('watchTime' === $activity['finishType']) {
            $result = $this->getTaskResultService()->getWatchTimeByActivityIdAndUserId($activityId, $this->getCurrentUser()->getId());

            $result /= 60;

            return !empty($result) && $result >= $activity['finishData'];
>>>>>>> 3cb8cbcd7bb55ffd28294506a7bcc2f79c90008f
        } else {
            $log = $this->getActivityLearnLogService()->getMyRecentFinishLogByActivityId($activityId);

            return !empty($log);
        }
    }

    public function get($targetId)
    {
<<<<<<< HEAD
        return array();
=======
        return [];
>>>>>>> 3cb8cbcd7bb55ffd28294506a7bcc2f79c90008f
    }

    public function find($targetIds, $showCloud = 1)
    {
<<<<<<< HEAD
        return array();
=======
        return [];
>>>>>>> 3cb8cbcd7bb55ffd28294506a7bcc2f79c90008f
    }

    public function findWithoutCloudFiles($targetIds)
    {
<<<<<<< HEAD
        return array();
=======
        return [];
>>>>>>> 3cb8cbcd7bb55ffd28294506a7bcc2f79c90008f
    }

    public function allowEventAutoTrigger()
    {
        return true;
    }

    final public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return mixed
     */
    protected function registerListeners()
    {
<<<<<<< HEAD
        return array();
=======
        return [];
>>>>>>> 3cb8cbcd7bb55ffd28294506a7bcc2f79c90008f
    }

    /**
     * @param string $eventName
     *
     * @return Listener
     */
    final public function getListener($eventName)
    {
        $map = $this->registerListeners();
        if (empty($map) || !isset($map[$eventName])) {
            return null;
        }
        $reflection = new \ReflectionClass($map[$eventName]);
<<<<<<< HEAD
        $listener = $reflection->newInstanceArgs(array($this->getBiz()));
=======
        $listener = $reflection->newInstanceArgs([$this->getBiz()]);
>>>>>>> 3cb8cbcd7bb55ffd28294506a7bcc2f79c90008f

        if (!$listener instanceof Listener) {
            throw new UnexpectedValueException('listener class must be Listener Derived Class');
        }

        return $listener;
    }

    protected function createNotFoundException($message = '')
    {
        return new NotFoundException($message);
    }

    protected function createAccessDeniedException($message = '')
    {
        return new AccessDeniedException($message);
    }

    protected function createInvalidArgumentException($message = '')
    {
        return new InvalidArgumentException($message);
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

    /**
     * @param $realDao
     *
     * @return mixed
     */
    protected function createDao($realDao)
    {
        return new DaoProxy($this->biz, $realDao, $this->biz['dao.metadata_reader'], $this->biz['dao.serializer'], $this->biz['dao.cache.array_storage']);
    }
}
