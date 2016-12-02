<?php

namespace Biz\Task\Strategy;


use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Topxia\Common\ArrayToolkit;

class BaseStrategy
{
    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function baseCreateTask($fields)
    {
        if ($this->invalidTask($fields)) {
            throw new InvalidArgumentException('task is invalid');
        }
        if (empty($fields['categoryId'])) {
            unset($fields['categoryId']);
        }
        if (!$this->getCourseService()->tryManageCourse($fields['fromCourseId'])) {
            throw new AccessDeniedException('无权创建任务');
        }

        $activity      = $this->getActivityService()->createActivity($fields);
        $currentNumber = $this->getTaskService()->getMaxNumberByCourseId($activity['fromCourseId']);

        $fields['activityId']    = $activity['id'];
        $fields['createdUserId'] = $activity['fromUserId'];
        $fields['courseId']      = $activity['fromCourseId'];
        $fields['seq']           = $this->getCourseService()->getNextCourseItemSeq($activity['fromCourseId']);
        $fields['number']        = $currentNumber + 1;

        $fields = ArrayToolkit::parts($fields, array(
            'courseId',
            'seq',
            'number',
            'mode',
            'categoryId',
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

    public function baseUpdateTask($id, $fields)
    {
        $savedTask = $this->getTaskService()->getTask($id);

        if (!$this->getCourseService()->tryManageCourse($savedTask['courseId'])) {
            throw $this->createAccessDeniedException('无权更新任务');
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

    public function baseFindCourseItems($courseId)
    {
        $items = array();
        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);
        foreach ($tasks as $task) {
            $task['itemType']            = 'task';
            $items["task-{$task['id']}"] = $task;
        }

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $chapter) {
            $chapter['itemType']               = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        return $items;
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

    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->biz->service('Task:TaskDao');
    }

    /**
     * @return CourseService
     */
    public function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

}