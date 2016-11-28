<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Task\Dao\TaskDao;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\Exception\AccessDeniedException;

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

        $fields = $this->filterFields($fields);

        $fields['activityId']    = $activity['id'];
        $fields['createdUserId'] = $activity['fromUserId'];
        $fields['courseId']      = $activity['fromCourseId'];

        return $this->getTaskDao()->create($fields);
    }

    public function updateTask($id, $fields)
    {
        $savedTask = $this->getTask($id);

        if (!$this->canManageCourse($savedTask['courseId'])) {
            throw new AccessDeniedException();
        }
        $this->getActivityService()->updateActivity($savedTask['activityId'], $fields);

        $fields = $this->filterFields($fields);

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

    protected function filterFields($fields)
    {
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

        return $fields;
    }

    public function findDetailedTasksByCourseId($courseId, $userId)
    {
        if ($this->getCourseService()->isCourseStudent($courseId, $userId)) {
            return array();
        }
        $tasks = $this->findTasksByCourseId($courseId);
        if (empty($tasks)) {
            return $tasks;
        }
        $taskResults = $this->findTaskResultsByCourseId($courseId, $userId);
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
        $activities      = $this->getActivityService()->getActivities(array_column($tasks, 'activityId'));
        $activityMap     = array();
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

    public function findTaskResultsByCourseId($courseId, $userId)
    {
        return $this->getTaskResultDao()->findByCourseId($courseId, $userId);
    }

    public function findTaskResults($couseTaskId, $userId)
    {
        return $this->getTaskResultDao()->findByTaskId($courseTaskId, $userId);
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
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

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }
}
