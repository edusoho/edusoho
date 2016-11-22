<?php

namespace Biz\Task\Service\Impl;

use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Course\CourseService;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function getTask($id)
    {
        $task = $this->getTaskDao()->get($id);
        return $task;
    }

    public function getTaskByCourseIdAndActivityId($courseId, $activity)
    {
        return $this->getTaskDao()->getByCourseIdAndActivityId($courseId, $activity);
    }

    public function createTask($fields)
    {
        if ($this->invalidTask($fields)) {
            throw new \InvalidArgumentException('task is invalid');
        }

        if (!$this->canManageCourse($fields['fromCourseId'])) {
            throw new AccessDeniedException();
        }

        $activity = $this->getActivityService()->createActivity($fields);

        $fields['activityId']    = $activity['id'];
        $fields['createdUserId'] = $activity['fromUserId'];
        $fields['courseId']      = $activity['fromCourseId'];
        $currentSeq              = $this->getMaxSeqByCourseId($activity['fromCourseId']);
        $fields['seq']           = $currentSeq + 1;


        $fields = ArrayToolkit::parts($fields, array(
            'courseId',
            'seq',
            'courseChapterId',
            'activityId',
            'title',
            'isFree',
            'isOptional',
            'startTime',
            'endTime',
            'status',
            'createdUserId'
        ));
        return $this->getTaskDao()->create($fields);
    }

    public function updateTask($id, $fields)
    {
        $savedTask = $this->getTask($id);

        if (!$this->canManageCourse($savedTask['courseId'])) {
            throw new AccessDeniedException();
        }
        $this->getActivityService()->updateActivity($savedTask['activityId'], $fields);

        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'isFree',
            'isOptional',
            'startTime',
            'endTime',
            'status'
        ));

        return $this->getTaskDao()->update($id, $fields);
    }

    public function updateSeq($id, $fileds)
    {
        $fileds = ArrayToolkit::parts($fileds, array(
            'seq',
            'courseChapterId',
        ));
        return $this->getTaskDao()->update($id, $fileds);
    }

    public function deleteTask($id)
    {
        $task = $this->getTask($id);

        if (!$this->canManageCourse($task['courseId'])) {
            throw new AccessDeniedException();
        }

        $result = $this->getTaskDao()->delete($id);
        $this->getActivityService()->deleteActivity($task['activityId']);

        return $result;
    }

    public function findTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->findByCourseId($courseId);
    }

    public function findTasksWithLearningResultByCourseId($courseId)
    {
        $user = $this->getCurrentUser();
        if ($this->getCourseService()->isCourseStudent($courseId, $user->getId())) {
            return array();
        }

        $tasks = $this->findTasksByCourseId($courseId);

        if (empty($tasks)) {
            return array();
        }

        $taskResults = $this->getTaskResultService()->findUserTaskResultsByCourseId($courseId);
        $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');


        $activityConfigs = $this->getActivityService()->getActivityTypes();
        $activityIds     = ArrayToolkit::column($tasks, 'activityId');
        $that            = $this;
        $activities      = $this->getActivityService()->findActivities($activityIds);

        $activities = ArrayToolkit::index($activities, 'id');

        array_walk($tasks, function (&$task) use ($taskResults, $activityConfigs, $activities, $that) {
            foreach ($taskResults as $key => $result) {
                if ($key != $task['id']) {
                    continue;
                }

                if (empty($task['resultStatus']) || 'finish' == $result['status']) {
                    $task['resultStatus'] = $result;
                }
            }
            $activity     = $activities[$task['activityId']];
            $config       = $activityConfigs[$activity['mediaType']];
            $length       = $that->formatActivityLength($activity['length']);
            $activityMeta = array(
                'mediaType' => $activity['mediaType'],
                'startTime' => $activity['startTime'],
                'endTime'   => $activity['endTime'],
                'length'    => $length
            );

            $task['activityMeta'] = array_merge($config->getMetas(), $activityMeta);
        });
        return $tasks;
    }

    public function startTask($taskId)
    {
        $task = $this->tryTakeTask($taskId);

        $user = $this->getCurrentUser();

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (!empty($taskResult)) {
            return;
        }

        $taskResult = array(
            'activityId'   => $task['id'],
            'courseId'     => $task['courseId'],
            'courseTaskId' => $task['id'],
            'userId'       => $user['id']
        );

        $this->getTaskResultService()->createTaskResult($taskResult);
    }

    public function finishTask($taskId)
    {
        $task = $this->tryTakeTask($taskId);

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (empty($taskResult)) {
            throw new AccessDeniedException('该任务不在进行状态');
        }

        if ($taskResult['status'] === 'finish') {
            return;
        }

        $update['updatedTime']  = time();
        $update['status']       = 'finish';
        $update['finishedTime'] = time();
        $this->getTaskResultService()->updateTaskResult($taskResult['id'], $update);
    }

    public function tryTakeTask($taskId)
    {
        if (!$this->canLearnTask($taskId)) {
            throw new AccessDeniedException("the Task is Locked");
        }
        $task = $this->getTask($taskId);

        if (empty($task)) {
            throw new NotFoundException("task does not exist");
        }
        return $task;
    }

    public function getNextTask($taskId)
    {
        $task = $this->getTask($taskId);
        if ($this->isLastTask($task)) {
            return array();
        }

        if (!$this->canLearnTask($taskId)) {
            return array();
        }

        //if the task is first, when get next task, we need to know if the task if finish, if not  return null;
        if ($this->isFirstTask($task)) {
            $isTaskLearned = $this->isTaskLearned($taskId);
            if (!$isTaskLearned) {
                return array();
            }
        }
        return $this->getTaskDao()->getByCourseIdAndSeq($task['courseId'], $task['seq'] + 1);
    }


    public function canLearnTask($taskId)
    {
        $task = $this->getTask($taskId);
        $this->getCourseService()->tryTakeCourse($task['courseId']);

        if ($this->isFirstTask($task)) {
            return true;
        }

        if ($task['isOptional']) {
            return true;
        }

        //获取教学方法策略 新的 course 中应该纪录当前的教方法 teach method: freedom|order
        //先按照默认实现
        $preTask = $this->getTaskDao()->getByCourseIdAndSeq($task['courseId'], $task['seq'] - 1);
        if (empty($preTask)) {
            throw new NotFoundException("previous task does is lost");
        }
        $isTaskLearned = $this->isTaskLearned($preTask['id']);
        if ($isTaskLearned) {
            return true;
        }
        return false;
    }

    public function isTaskLearned($taskId)
    {
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        return empty($taskResult) ? false : ('finish' == $taskResult['status']);
    }

    public function getMaxSeqByCourseId($courseId)
    {
        return $this->getTaskDao()->getMaxSeqByCourseId($courseId);
    }

    public function findTasksByChapterId($chapterId)
    {
        return $this->getTaskDao()->findTasksByChapterId($chapterId);
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    protected function canManageCourse($courseId)
    {
        return true;
    }

    protected function formatActivityLength($len)
    {
        if (empty($len) || $len == 0) {
            return null;
        }
        $h = floor($len / 60);
        $m = fmod($len, 60);
        //TODO 目前没考虑秒
        return ($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m).':00';
    }

    protected function invalidTask($task)
    {
        if (!ArrayToolkit::requireds($task, array(
            'title',
            'fromCourseId'
        ))
        ) {
            return true;
        }

        return false;
    }

    protected function isFirstTask($task)
    {
        return 1 == $task['seq'];
    }

    protected function isLastTask($task)
    {
        $maxSeq = $this->getMaxSeqByCourseId($task['courseId']);
        return $maxSeq == $task['seq'];
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }
}
