<?php

namespace Biz\Task\Event;

use Biz\Course\Service\CourseService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Dao\TaskDao;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;
use Biz\Course\Copy\Chain\ActivityTestpaperCopy;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class TaskSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.create' => 'onCourseTaskCreate',
            'course.task.update' => 'onCourseTaskUpdate',
            'course.task.updateOptional' => 'onCourseTaskUpdate',
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

        //task 创建同步任务，永久有效
        $this->getSchedulerService()->register(array(
            'name' => 'course_task_create_sync_job_'.$task['id'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time()),
            'misfire_policy' => 'executing',
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

        //task 更新同步任务，永久有效
        $this->getSchedulerService()->register(array(
            'name' => 'course_task_update_sync_job_'.$task['id'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time()),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Task\Job\CourseTaskUpdateSyncJob',
            'args' => array('taskId' => $task['id']),
        ));
    }

    public function onCourseTaskPublish(Event $event)
    {
        $task = $event->getSubject();
        $this->syncTaskStatus($task, true);

        $this->dispatchEvent('course.task.publish.sync', new Event($task));
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

        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        $status = $published ? 'published' : 'unpublished';

        if (CourseService::DEFAULT_COURSE_TYPE === $course['courseType']) {
            $sameCategoryTasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
            $this->getTaskDao()->update(array('courseIds' => array_column($copiedCourses, 'id'), 'copyIds' => array_column($sameCategoryTasks, 'id')), array('status' => $status));
        } else {
            $this->getTaskDao()->update(array('courseIds' => array_column($copiedCourses, 'id'), 'copyId' => $task['id']), array('status' => $status));
        }
    }

    public function onCourseTaskDelete(Event $event)
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
            'name' => 'course_task_delete_sync_job_'.$task['id'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time()),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Task\Job\CourseTaskDeleteSyncJob',
            'args' => array('taskId' => $task['id'], 'courseId' => $task['courseId']),
        ));
    }

    protected function syncTestpaper($activity, $copiedCourse)
    {
        if ('testpaper' != $activity['mediaType']) {
            return array();
        }

        $testpaperCopy = new ActivityTestpaperCopy($this->getBiz());

        return $testpaperCopy->copy($activity, array(
            'newCourseSetId' => $copiedCourse['courseSetId'],
            'newCourseId' => $copiedCourse['id'],
            'isCopy' => 1,
        ));
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
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        $biz = $this->getBiz();

        return $biz['dispatcher']->dispatch($eventName, $event);
    }
}
