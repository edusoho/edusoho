<?php

namespace CourseTask\Service\Task\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use CourseTask\Service\Task\TaskService;

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
            throw $this->createAccessDeniedException();
        }

        $activity                = $this->getActivityService()->createActivity($fields);
        $fields['activityId']    = $activity['id'];
        $fields['createdUserId'] = $this->getCurrentUser()->getId();
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

        return $this->getTaskDao()->add($fields);
    }

    public function updateTask($id, $fields)
    {
        $savedTask = $this->getTask($id);

        if (!$this->canManageCourse($savedTask['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $activity = $this->getActivityService()->updateActivity($savedTask['activityId'], $fields);

        return $this->getTaskDao()->update($id, $fields);
    }

    public function deleteTask($id)
    {
        $task = $this->getTask($id);

        if (!$this->canManageCourse($task['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $result = $this->getTaskDao()->delete($id);
        $this->getActivityService()->deleteActivity($task['activityId']);

        return $result;
    }

    public function findTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->findByCourseId($courseId);
    }

    protected function getTaskDao()
    {
        return $this->createDao('Task:Task.TaskDao');
    }

    protected function canManageCourse($courseId)
    {
        return true;
    }

    protected function invalidTask($task)
    {
        if (!ArrayToolkit::requireds($task, array(
            'title',
            'fromCourseId'
        ))) {
            return true;
        }

        return false;
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:Activity.ActivityService');
    }
}
