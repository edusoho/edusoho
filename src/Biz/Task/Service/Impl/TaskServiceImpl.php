<?php

namespace Biz\Task\Service\Impl;

use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\AccessDeniedException;
use Topxia\Common\Exception\ResourceNotFoundException;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Course\CourseService;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
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

        $fields = ArrayToolkit::parts($fields, array(
            'courseId',
            'preTaskId',
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

    public function findUserTasksByCourseId($courseId, $userId)
    {
        if ($this->getCourseService()->isCourseStudent($courseId, $userId)) {
            return array();
        }

        $tasks = $this->findTasksByCourseId($courseId);

        if (empty($tasks)) {
            return array();
        }

        $taskResults = $this->getTaskResultService()->findUserTaskResultsByCourseId($courseId);

        if (!empty($taskResults)) {

            foreach ($taskResults as $tr) {
                foreach ($tasks as $tk => $t) {
                    if ($tr['courseTaskId'] != $t['id']) {
                        continue;
                    }
                    if (!isset($t['task_result']) || !$t['task_result']['status'] == 'finish') {
                        $tasks[$tk]['task_result'] = $tr;
                        break;
                    }
                }
            }
        }

        $activityConfigs = $this->getActivityService()->getActivityTypes();
        $activityIds     = ArrayToolkit::column($tasks, 'activityId');

        $activities = $this->getActivityService()->getActivities($activityIds);

        $activityMap = array();
        foreach ($activities as $act) {
            $activityMap[$act['id']] = $act;
        }

        foreach ($tasks as $tk => $t) {
            $act                         = $activityMap[$t['activityId']];
            $config                      = $activityConfigs[$act['mediaType']];
            $tasks[$tk]['activity_meta'] = array_merge($config->getMetas(), array(
                'length' => $this->formatActivityLength($act['length']), 
                'mediaType' => $act['mediaType'],
                'startTime' => $act['startTime'], 
                'endTime' => $act['endTime'], 
                'finished' => !empty($act['endTime']) && $act['mediaType'] == 'live' ? ($act['endTime'] < time() ? 1 : 0) : 0)
            );
        }

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

        if($taskResult['status'] === 'finish'){
            return;
        }

        $update['updatedTime'] = time();
        $update['status']      = 'finish';
        $update['finishedTime']= time();
        $this->getTaskResultService()->updateTaskResult($taskResult['id'], $update);
    }

    public function tryTakeTask($taskId)
    {
        $task = $this->getTask($taskId);

        if (empty($task)) {
            throw new ResourceNotFoundException('task', $taskId);
        }

        $this->getCourseService()->tryTakeCourse($task['courseId']);

        return $task;
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
        return ($h < 10 ? '0' . $h : $h) . ':' . ($m < 10 ? '0' . $m : $m) . ':00';
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
