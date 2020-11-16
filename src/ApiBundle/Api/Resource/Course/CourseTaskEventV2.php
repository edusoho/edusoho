<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\Visualization\Service\DataCollectService;

class CourseTaskEventV2 extends AbstractResource
{
    const EVENT_START = 'start';

    const EVENT_DOING = 'doing';

    const EVENT_WATCHING = 'watching';

    const EVENT_FINISH = 'finish';

    public function update(ApiRequest $request, $courseId, $taskId, $event)
    {
        $this->checkEvents($request, $event, $taskId, $courseId);
        $data = $request->request->all();

        if (self::EVENT_START === $event) {
            return $this->start($request, $courseId, $taskId, $data);
        }

        if (self::EVENT_DOING === $event) {
            return $this->doing($request, $courseId, $taskId, $data);
        }

        if (self::EVENT_FINISH === $event) {
            return $this->finish($request, $courseId, $taskId, $data);
        }

        if (self::EVENT_WATCHING === $event) {
            return $this->watching($request, $courseId, $taskId, $data);
        }
    }

    protected function start(ApiRequest $request, $courseId, $taskId, $data)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $sign = date('YmdHis').'-'.$user['id'].'-'.$activity['id'].'-'.substr(md5($user['id'].$data['client'].microtime()), 0, 6);
        $flow = $this->getDataCollectService()->createLearnFlow($user['id'], $activity['id'], $sign);
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'event' => self::EVENT_START,
            'client' => $data['client'],
            'startTime' => $currentTime,
            'endTime' => $currentTime,
            'duration' => 0,
            'mediaType' => $activity['mediaType'],
            'flowSign' => $flow['sign'],
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
            ],
        ]);

        $triggerData = ['lastTime' => $record['endTime']];
        $result = $this->getTaskService()->trigger($taskId, self::EVENT_START, $triggerData);

        if (self::EVENT_FINISH === $result['status']) {
            $nextTask = $this->getTaskService()->getNextTask($taskId);
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);
            $completionRate = $progress['percent'];
        } else {
            $nextTask = null;
            $completionRate = null;
        }

        return [
            'taskResult' => $result,
            'nextTask' => $nextTask,
            'completionRate' => $completionRate,
            'record' => $record,
        ];
    }

    protected function doing(ApiRequest $request, $courseId, $taskId, $data)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'event' => self::EVENT_DOING,
            'client' => $data['client'],
            'startTime' => $data['startTime'],
            'endTime' => $data['startTime'] + $data['duration'],
            'duration' => $data['duration'],
            'mediaType' => $activity['mediaType'],
            'flowSign' => $data['sign'],
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
            ],
        ]);
        $triggerData = ['lastTime' => $record['endTime']];
        $result = $this->getTaskService()->trigger($taskId, self::EVENT_DOING, $triggerData);

        if (self::EVENT_FINISH === $result['status']) {
            $nextTask = $this->getTaskService()->getNextTask($taskId);
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);
            $completionRate = $progress['percent'];
        } else {
            $nextTask = null;
            $completionRate = null;
        }

        return [
            'taskResult' => $result,
            'nextTask' => $nextTask,
            'completionRate' => $completionRate,
            'record' => $record,
        ];
    }

    protected function finish(ApiRequest $request, $courseId, $taskId, $data)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'event' => self::EVENT_FINISH,
            'client' => $data['client'],
            'startTime' => $data['startTime'],
            'endTime' => $data['startTime'] + $data['duration'],
            'duration' => $data['duration'],
            'mediaType' => $activity['mediaType'],
            'flowSign' => $data['sign'],
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
            ],
        ]);

        $triggerData = ['lastTime' => $record['endTime']];
        $result = $this->getTaskService()->trigger($taskId, self::EVENT_FINISH, $triggerData);

        if (self::EVENT_FINISH === $result['status']) {
            $nextTask = $this->getTaskService()->getNextTask($taskId);
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);
            $completionRate = $progress['percent'];
        } else {
            $nextTask = null;
            $completionRate = null;
        }

        return [
            'taskResult' => $result,
            'nextTask' => $nextTask,
            'completionRate' => $completionRate,
            'record' => $record,
        ];
    }

    protected function watching(ApiRequest $request, $courseId, $taskId, $data)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'event' => self::EVENT_WATCHING,
            'client' => $data['client'],
            'startTime' => $data['startTime'],
            'endTime' => $data['startTime'] + $data['duration'],
            'duration' => $data['duration'],
            'mediaType' => $activity['mediaType'],
            'flowSign' => $data['sign'],
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
            ],
        ]);

        $triggerData = ['lastTime' => $record['endTime']];
        $result = $this->getTaskService()->trigger($taskId, self::EVENT_DOING, $triggerData);

        if (self::EVENT_FINISH === $result['status']) {
            $nextTask = $this->getTaskService()->getNextTask($taskId);
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);
            $completionRate = $progress['percent'];
        } else {
            $nextTask = null;
            $completionRate = null;
        }

        return [
            'taskResult' => $result,
            'nextTask' => $nextTask,
            'completionRate' => $completionRate,
            'record' => $record,
        ];
    }

    protected function checkEvents(ApiRequest $request, $eventName, $taskId, $courseId)
    {
        if (!in_array($eventName, [self::EVENT_START, self::EVENT_DOING, self::EVENT_WATCHING, self::EVENT_FINISH], true)) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (('start' !== $eventName) && empty($request->request->get('sign'))) {
            throw CommonException::ERROR_PARAMETER();
        }

//        if ((self::EVENT_START === $eventName) && $this->getTaskService()->canStartTask($taskId)) {
//            throw TaskException::LOCKED_TASK();
//        }
    }

    /**
     * @return DataCollectService
     */
    private function getDataCollectService()
    {
        return $this->service('Visualization:DataCollectService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }
}
