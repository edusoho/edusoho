<?php

namespace Biz\Task\Strategy;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Context\Biz;
use AppBundle\Common\ArrayToolkit;

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

    public function createTask($fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'courseId',
            'fromCourseSetId',
            'seq',
            'mode',
            'categoryId',
            'activityId',
            'title',
            'type',
            'mediaSource',
            'isFree',
            'isOptional',
            'startTime',
            'endTime',
            'length',
            'status',
            'createdUserId',
        ));

        return $this->getTaskDao()->create($fields);
    }

    public function updateTask($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'isFree',
            'isOptional',
            'startTime',
            'endTime',
            'length',
            'status',
            'mediaSource',
        ));

        return $this->getTaskDao()->update($id, $fields);
    }

    protected function invalidTask($task)
    {
        if (!ArrayToolkit::requireds($task, array(
            'title',
            'fromCourseId',
        ))
        ) {
            return true;
        }

        return false;
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
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }

    /**
     * @return ActivityService
     */
    public function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getCourseLessonService()
    {
        return $this->biz->service('Course:LessonService');
    }
}
