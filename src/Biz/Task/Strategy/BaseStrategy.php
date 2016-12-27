<?php

namespace Biz\Task\Strategy;

use Biz\Task\Dao\TaskDao;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Context\Biz;
use Biz\Activity\Service\ActivityService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class BaseStrategy
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function baseCreateTask($fields)
    {
        $fields = array_filter($fields);
        if ($this->invalidTask($fields)) {
            throw new InvalidArgumentException('task is invalid');
        }

        if (!$this->getCourseService()->tryManageCourse($fields['fromCourseId'])) {
            throw new AccessDeniedException('无权创建任务');
        }
        $activity = $this->getActivityService()->createActivity($fields);

        $fields['activityId']    = $activity['id'];
        $fields['createdUserId'] = $activity['fromUserId'];
        $fields['courseId']      = $activity['fromCourseId'];
        $fields['seq']           = $this->getCourseService()->getNextCourseItemSeq($activity['fromCourseId']);

        $fields = ArrayToolkit::parts($fields, array(
            'courseId',
            'seq',
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
            throw new AccessDeniedException('无权更新任务');
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
        $tasks = $this->getTaskService()->findTasksFetchActivityAndResultByCourseId($courseId);
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

    /**
     * @return CourseChapterDao
     */
    public function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    public function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
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
    public function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
