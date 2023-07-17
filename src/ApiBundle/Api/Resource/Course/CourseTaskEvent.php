<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\DeviceToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\Visualization\Service\DataCollectService;

class CourseTaskEvent extends AbstractResource
{
    const EVENT_START = 'start';
    const EVENT_DOING = 'doing';
    const EVENT_FINISH = 'finish';

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function update(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        if (!in_array($eventName, [self::EVENT_START, self::EVENT_DOING, self::EVENT_FINISH], true)) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (self::EVENT_START === $eventName) {
            if ($this->getTaskService()->canStartTask($taskId)) {
                return $this->start($request, $courseId, $taskId, self::EVENT_START);
            }

            throw TaskException::LOCKED_TASK();
        }

        if (self::EVENT_DOING === $eventName) {
            return $this->doing($request, $courseId, $taskId, $eventName);
        }

        if (self::EVENT_FINISH === $eventName) {
            return $this->finish($request, $courseId, $taskId, $eventName);
        }

        throw CommonException::ERROR_PARAMETER();
    }

    private function start(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $user = $this->getCurrentUser();
        $sign = $request->request->get('sign');
        $version = (int) $request->request->get('version', '1');
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        /**
         * 通过UA只能判断出iOS APP, Android APP, H5和新版旧版微网校会随着发布一起加上client字段，暂无法判断小程序，所以unknown的全部被认为是小程序
         */
        $client = $request->request->get('client', '');
        if (empty($client)) {
            $client = $this->getClient($request);
        }
        if (empty($sign)) {
            if (1 === $version) {
                $sign = $user['loginToken'].'-'.$user['id'].'-'.$activity['id'].'-'.substr(md5($user['id']), 0, 6);
            } elseif (2 === $version) {
                $sign = date('YmdHis').'-'.$user['id'].'-'.$activity['id'].'-'.substr(md5($user['id']), 0, 6);
            } else {
                throw CommonException::ERROR_PARAMETER();
            }
        }

        $flow = $this->getDataCollectService()->getFlowBySign($user['id'], $sign);

        if (empty($flow)) {
            $this->getDataCollectService()->createLearnFlow($user['id'], $activity['id'], $sign);
        }
        $currentTime = time();
        $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'event' => self::EVENT_START,
            'client' => $client,
            'startTime' => $currentTime,
            'endTime' => $currentTime,
            'duration' => 0,
            'mediaType' => $activity['mediaType'],
            'flowSign' => $sign,
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
            ],
        ]);
        $return = $this->doing($request, $courseId, $taskId, $eventName);

        return [
            'result' => $return['result'],
            'event' => $eventName,
            'nextTask' => $return['nextTask'],
            'lastTime' => time(),
            'completionRate' => $return['completionRate'],
        ];
    }

    private function doing(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $lastTime = $request->request->get('lastTime', time());
        $watchTime = $request->request->get('watchTime', 0);

        $data = ['lastTime' => $lastTime];
        if (!empty($watchTime)) {
            $data['events']['watching']['watchTime'] = $watchTime;
        }
        $user = $this->getCurrentUser();
        $sign = $request->request->get('sign');
        $version = (int) $request->request->get('version', '1');
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        /**
         * 通过UA只能判断出iOS APP, Android APP, H5和新版旧版微网校会随着发布一起加上client字段，暂无法判断小程序，所以unknown的全部被认为是小程序
         */
        $client = $request->request->get('client', '');
        if (empty($client)) {
            $client = $this->getClient($request);
        }
        if (empty($sign)) {
            if (1 === $version) {
                $sign = $user['loginToken'].'-'.$user['id'].'-'.$activity['id'].'-'.substr(md5($user['id']), 0, 6);
            } elseif (2 === $version) {
                $sign = date('YmdHis').'-'.$user['id'].'-'.$activity['id'].'-'.substr(md5($user['id']), 0, 6);
            } else {
                throw CommonException::ERROR_PARAMETER();
            }
        }

        $flow = $this->getDataCollectService()->getFlowBySign($user['id'], $sign);

        if (empty($flow)) {
            $this->getDataCollectService()->createLearnFlow($user['id'], $activity['id'], $sign);
        }
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'event' => self::EVENT_DOING,
            'client' => $client,
            'startTime' => $lastTime,
            'endTime' => $currentTime,
            'duration' => $currentTime - $lastTime, //这里需要做校验，兼容老数据
            'mediaType' => $activity['mediaType'],
            'flowSign' => $sign,
            'data' => array_merge([
                'userAgent' => $request->headers->get('user-agent'),
            ], $data),
        ]);

        $result = $this->getTaskService()->trigger($taskId, $eventName, $data);

        if (self::EVENT_FINISH === $result['status']) {
            $nextTask = $this->getTaskService()->getNextTask($taskId);
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);
            $completionRate = $progress['percent'];
        } else {
            $nextTask = null;
            $completionRate = null;
        }

        return [
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $nextTask,
            'lastTime' => time(),
            'completionRate' => $completionRate,
        ];
    }

    private function finish(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if ('published' !== $task['status']) {
            throw TaskException::UNPUBLISHED_TASK();
        }

        if (!$this->getTaskService()->isFinished($taskId)) {
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);
            $learningProgress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $taskResult['userId']);

            return [
                'result' => $taskResult,
                'event' => $eventName,
                'nextTask' => null,
                'completionRate' => $learningProgress['percent'],
            ];
        }

        $result = $this->getTaskService()->finishTaskResult($taskId);

        $nextTask = $this->getTaskService()->getNextTask($taskId);
        $learningProgress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);

        return [
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $nextTask ?: null,
            'completionRate' => $learningProgress['percent'],
        ];
    }

    protected function getClient(ApiRequest $request)
    {
        $userAgent = $request->headers->get('user-agent');

        return DeviceToolkit::getClient($userAgent);
    }

    /**
     * @return TaskResultService
     */
    private function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }

    /**
     * @return DataCollectService
     */
    private function getDataCollectService()
    {
        return $this->service('Visualization:DataCollectService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return object|\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage|null
     */
    protected function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }
}
