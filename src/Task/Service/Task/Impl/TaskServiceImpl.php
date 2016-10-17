<?php

namespace Task\Service\Task\Impl;

use Topxia\Common\ArrayToolkit;
use Task\Service\Task\TaskService;
use Topxia\Service\Common\BaseService;
use Task\Service\Task\TaskProcessorFactory;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
    }

    public function createTask($task)
    {
        if ($this->invalidTask($task)) {
            throw new \InvalidArgumentException('task is invalid');
        }

        if (!$this->canManageCourse($task['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $processor = TaskProcessorFactory::getTaskProcessor($task['mediaType']);
        if (!empty($processor)) {
            $media           = $processor->create($task);
            $task['mediaId'] = $media['id'];
        }

        $fields = ArrayToolkit::parts($task, array(
            'title',
            'desc',
            'mediaId',
            'mediaType',
            'content',
            'length',
            'fromCourseId',
            'fromCourseSetId',
            'fromUserId',
            'startTime',
            'endTime'
        ));

        $fields['fromUserId'] = $this->getCurrentUser()->getId();

        return $this->getTaskDao()->add($fields);
    }

    public function updateTask($id, $fields)
    {
        $savedTask = $this->getTask($id);

        if (!$this->canManageCourse($savedTask['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $processor = TaskProcessorFactory::getTaskProcessor($savedTask['mediaType']);

        if (!empty($processor) && !empty($savedTask['mediaId'])) {
            $media = $processor->update($savedTask['mediaId'], $fields);
        }

        return $this->getTaskDao()->update($id, $fields);
    }

    public function deleteTask($id)
    {
        $task = $this->getTask($id);

        if (!$this->canManageCourse($task['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $processor = TaskProcessorFactory::getTaskProcessor($task['mediaType']);
        if (!empty($processor) && !empty($savedTask['mediaId'])) {
            $processor->delete($task['mediaId']);
        }

        return $this->getTaskDao()->delete($id);
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
            'mediaType',
            'fromCourseId',
            'fromCourseSetId'
        ))) {
            return true;
        }

        if (!in_array($task['mediaType'], $this->getMediaTypes())) {
            return true;
        }

        return false;
    }

    protected function getMediaTypes()
    {
        return TaskProcessorFactory::getTaskTypes();
    }
}
