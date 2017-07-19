<?php

namespace Biz\Task\Event;

use Biz\Course\Service\CourseService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Dao\TaskDao;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;
use Biz\Course\Copy\Impl\ActivityTestpaperCopy;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class TaskSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.create' => 'onCourseTaskCreate',
            'course.task.update' => 'onCourseTaskUpdate',
            'course.task.delete' => 'onCourseTaskDelete',
            'course.task.publish' => 'onCourseTaskPublish',
            'course.task.unpublish' => 'onCourseTaskUnpublish',
        );
    }

    public function onCourseTaskCreate(Event $event)
    {
        $task = $event->getSubject();
        if ($task['copyId'] > 0) {
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }

        $this->getSchedulerService()->register(array(
            'name' => 'course_task_create_sync_job',
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time()),
            'class' => 'Biz\Task\Job\CourseTaskCreateSyncJob',
            'args' => array('taskId' => $task['id']),
        ));
    }

    public function onCourseTaskUpdate(Event $event)
    {
        $task = $event->getSubject();
        if ($task['copyId'] > 0) {
            return;
        }

        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }

        $this->getSchedulerService()->register(array(
            'name' => 'course_task_update_sync_job',
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time()),
            'class' => 'Biz\Task\Job\CourseTaskUpdateSyncJob',
            'args' => array('taskId' => $task['id']),
        ));
    }

    public function onCourseTaskPublish(Event $event)
    {
        $task = $event->getSubject();
        $this->syncTaskStatus($task, true);
    }

    public function onCourseTaskUnpublish(Event $event)
    {
        $task = $event->getSubject();
        $this->syncTaskStatus($task, false);
    }

    protected function syncTaskStatus($task, $published)
    {
        if ($task['copyId'] > 0) {
            return;
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        $status = $published ? 'published' : 'unpublished';

        if ($course['courseType'] === CourseService::DEFAULT_COURSE_TYPE) {
            $sameCategoryTasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
            $this->getTaskDao()->update(array('copyIds' => array_column($sameCategoryTasks, 'id')), array('status' => $status));
        } else {
            $this->getTaskDao()->update(array('copyId' => $task['id']), array('status' => $status));
        }

    }

    public function onCourseTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        if ($task['copyId'] > 0) {
            return;
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        $tasks = array($task);
        if ($course['courseType'] === CourseService::DEFAULT_COURSE_TYPE) {
            $sameCategoryTasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
            $tasks = $sameCategoryTasks;
        }

        if (!empty($tasks)) {
            $this->getTaskDao()->delete(array('copyIds' => array_column($tasks, 'id')));
        }

    }

    protected function syncTestpaper($activity, $copiedCourse)
    {
        if ($activity['mediaType'] != 'testpaper') {
            return array();
        }

        $testpaperCopy = new ActivityTestpaperCopy($this->getBiz());

        return $testpaperCopy->copy($activity, array(
            'newCourseSetId' => $copiedCourse['courseSetId'],
            'newCourseId' => $copiedCourse['id'],
            'isCopy' => 1,
        ));
    }

    protected function deleteTask($taskId, $course)
    {
        return  $this->createCourseStrategy($course)->deleteTask($this->getTaskDao()->get($taskId));
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return $this->getBiz()->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    protected function getActivityConfig($type)
    {
        $biz = $this->getBiz();

        return $biz["activity_type.{$type}"];
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('');
    }
}
