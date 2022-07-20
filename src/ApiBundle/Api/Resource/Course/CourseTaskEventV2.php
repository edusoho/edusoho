<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Visualization\Service\DataCollectService;
use Biz\Visualization\Service\LearnControlService;

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
    }

    protected function start(ApiRequest $request, $courseId, $taskId, $data)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $sign = date('YmdHis').'-'.$user['id'].'-'.$activity['id'].'-'.substr(md5($user['id'].$data['client'].microtime()), 0, 6);
        list($canCreate, $denyReason) = $this->getLearnControlService()->checkCreateNewFlow($user['id'], $request->request->get('lastSign', ''));
        if (!$canCreate) {
            return [
                'taskResult' => null,
                'nextTask' => null,
                'completionRate' => null,
                'record' => null,
                'learnControl' => [
                    'allowLearn' => false,
                    'denyReason' => $denyReason,
                ],
                'learnedTime' => $this->getMyLearnedTime($activity),
            ];
        }
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

        $active = $request->request->get('release', 0) ? 0 : 1;
        $this->getDataCollectService()->updateLearnFlow($flow['id'], ['lastLearnTime' => $record['endTime'], 'active' => $active]);
        $this->getTaskService()->startTask($taskId);
        $result = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

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
            'nextTask' => empty($nextTask) ? null : $nextTask,
            'completionRate' => $completionRate,
            'record' => $record,
            'learnControl' => [
                'allowLearn' => true,
                'denyReason' => '',
            ],
            'learnedTime' => $this->getMyLearnedTime($activity),
        ];
    }

    protected function doing(ApiRequest $request, $courseId, $taskId, $data)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        if (in_array($task['type'], ['live', 'testpaper', 'homework'])) {
            list($canDoing, $denyReason) = [true, ''];
        } else {
            list($canDoing, $denyReason) = $this->getLearnControlService()->checkActive($user['id'], $data['sign'], $request->request->get('reActive', 1));
        }
        if (!empty($data['watchData'])) {
            $watchData = $data['watchData'];
            if (isset($watchData['duration']) && $watchData['duration'] > $data['duration']) {
                $data['originDuration'] = $data['duration'];
                $data['duration'] = $watchData['duration'];
            }
        }
        $flow = $this->getDataCollectService()->getFlowBySign($user['id'], $data['sign']);
        $currentTime = time();

        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'status' => $request->request->get('status', 0),
            'event' => self::EVENT_DOING,
            'client' => $data['client'],
            'startTime' => $currentTime - $flow['lastLearnTime'] - 20 > $data['duration'] ? $currentTime - $data['duration'] : $flow['lastLearnTime'],
            'endTime' => $currentTime,
            'duration' => $currentTime - $flow['lastLearnTime'] - 20 > $data['duration'] ? $data['duration'] : $currentTime - $flow['lastLearnTime'],
            'mediaType' => $activity['mediaType'],
            'flowSign' => $data['sign'],
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
                'data' => $data,
            ],
        ]);
        if (!empty($data['duration'])) {
            $this->getDataCollectService()->updateLearnFlow($flow['id'], ['lastLearnTime' => $record['endTime']]);
            $this->getTaskService()->doTask($taskId, $record['duration']);
        }

        $watchResult = null;
        if (!empty($data['watchData'])) {
            $watchResult = $this->watching($request, $courseId, $taskId, $data, $record);
        }

        if ($this->getTaskService()->isFinished($task['id'])) {
            $this->getTaskService()->finishTaskResult($task['id']);
        }
        $triggerData = ['duration' => $record['duration'], 'lastTime' => $record['startTime'], 'events' => $request->request->get('events', [])];
        $result = $this->getTaskService()->trigger($taskId, self::EVENT_DOING, $triggerData);
        if (isset($data['lastLearnTime'])) {
            $this->getTaskResultService()->updateTaskResult($result['id'], ['lastLearnTime' => $data['lastLearnTime']]);
        }

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
            'nextTask' => empty($nextTask) ? null : $nextTask,
            'completionRate' => $completionRate,
            'record' => $record,
            'watchResult' => empty($watchResult) ? null : $watchResult,
            'learnControl' => [
                'allowLearn' => $canDoing,
                'denyReason' => $denyReason,
            ],
            'learnedTime' => $this->getMyLearnedTime($activity),
        ];
    }

    protected function finish(ApiRequest $request, $courseId, $taskId, $data)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        $course = $this->getCourseService()->getCourse($courseId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if ('live' == $task['type'] && 'time' == $activity['finishType']) {
            return $this->doing($request, $courseId, $taskId, $data);
        }
        list($canDoing, $denyReason) = $this->getLearnControlService()->checkActive($user['id'], $data['sign'], $request->request->get('reActive', 0));
        $flow = $this->getDataCollectService()->getFlowBySign($user['id'], $data['sign']);
        $currentTime = time();
        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'status' => $request->request->get('status', 0),
            'event' => self::EVENT_FINISH,
            'client' => $data['client'],
            'startTime' => $currentTime - $flow['lastLearnTime'] - 20 > $data['duration'] ? $currentTime - $data['duration'] : $flow['lastLearnTime'],
            'endTime' => $currentTime,
            'duration' => $currentTime - $flow['lastLearnTime'] - 20 > $data['duration'] ? $data['duration'] : $currentTime - $flow['lastLearnTime'],
            'mediaType' => $activity['mediaType'],
            'flowSign' => $data['sign'],
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
            ],
        ]);

        $triggerData = [
            'lastTime' => $record['endTime'],
            'finish' => [
                'data' => [],
            ],
        ];
        $result = $this->getTaskService()->trigger($taskId, self::EVENT_FINISH, $triggerData);
        if ($course['enableFinish']) {
            $result = $this->getTaskService()->finishTaskResult($taskId);
        }

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
            'nextTask' => empty($nextTask) ? null : $nextTask,
            'completionRate' => $completionRate,
            'record' => $record,
            'learnControl' => [
                'allowLearn' => $canDoing,
                'denyReason' => $denyReason,
            ],
            'learnedTime' => $this->getMyLearnedTime($activity),
        ];
    }

    protected function watching(ApiRequest $request, $courseId, $taskId, $data, $record)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($taskId);
        if ('video' !== $task['type']) {
            return [
                'taskResult' => null,
                'record' => null,
            ];
        }
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $watchData = $data['watchData'];
        $currentTime = time();
        $flow = $this->getDataCollectService()->getFlowBySign($user['id'], $data['sign']);
        $record = $this->getDataCollectService()->push([
            'userId' => $user['id'],
            'activityId' => $task['activityId'],
            'taskId' => $task['id'],
            'status' => $request->request->get('status', 0),
            'courseId' => $task['courseId'],
            'courseSetId' => $task['fromCourseSetId'],
            'event' => self::EVENT_WATCHING,
            'client' => $data['client'],
            'startTime' => $currentTime - $flow['lastWatchTime'] - 20 > $watchData['duration'] ? $currentTime - $watchData['duration'] : $flow['lastWatchTime'],
            'endTime' => $currentTime,
            'duration' => $currentTime - $flow['lastWatchTime'] - 20 > $watchData['duration'] ? $watchData['duration'] : $currentTime - $flow['lastWatchTime'],
            'mediaType' => $activity['mediaType'],
            'flowSign' => $data['sign'],
            'data' => [
                'userAgent' => $request->headers->get('user-agent'),
                'watchData' => $watchData,
            ],
        ]);

        $result = $this->getTaskService()->watchTask($taskId, $record['duration']);
        $this->getDataCollectService()->updateLearnFlow($flow['id'], ['lastWatchTime' => $record['endTime']]);

        return [
            'taskResult' => $result,
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
    }

    protected function getMyLearnedTime(array $activity)
    {
        if ('watchTime' !== $activity['finishType']) {
            $learnedTime = $this->getTaskResultService()->getMyLearnedTimeByActivityId($activity['id']);

            return (int) $learnedTime;
        }

        $watchTime = $this->getTaskResultService()->getWatchTimeByActivityIdAndUserId($activity['id'], $this->getCurrentUser()->getId());
        if (empty($watchTime)) {
            return 0;
        }

        return (int) $watchTime;
    }

    protected function getKickOutStatus($userId, $sign)
    {
        $this->getLearnControlService()->checkActive($userId, $sign);
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

    /**
     * @return LearnControlService
     */
    private function getLearnControlService()
    {
        return $this->service('Visualization:LearnControlService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }

    /*
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
