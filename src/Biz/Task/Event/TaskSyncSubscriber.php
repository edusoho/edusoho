<?php

namespace Biz\Task\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Constant\CourseType;
use Biz\Course\Event\CourseSyncSubscriber;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Constant\LogModule;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

/**
 * 任务执行超时
 * 任务执行错误(异常)
 * 任务执行结果不正确?
 */
class TaskSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'course.task.create' => 'onCourseTaskCreate',
            'course.task.update' => 'onCourseTaskUpdate',
            'course.task.updateOptional' => 'onCourseTaskUpdate',
            'course.task.delete' => 'onCourseTaskDelete',
            'course.task.publish' => 'onCourseTaskPublish',
            'course.task.unpublish' => 'onCourseTaskUnpublish',
        ];
    }

    public function onCourseTaskCreate(Event $event)
    {
        $task = $event->getSubject();
        if ($task['copyId'] > 0) {
            return;
        }
        $syncCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if (empty($syncCourses)) {
            return;
        }
        $this->syncForCreateTask($task, $syncCourses);
        //task 创建同步任务，永久有效
        $this->getSchedulerService()->register([
            'name' => 'course_task_create_sync_job_'.$task['id'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => time(),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Task\Job\CourseTaskCreateSyncJob',
            'args' => ['taskId' => $task['id']],
        ]);
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
        $this->getSchedulerService()->register([
            'name' => 'course_task_update_sync_job_'.$task['id'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => time(),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Task\Job\CourseTaskUpdateSyncJob',
            'args' => ['taskId' => $task['id']],
        ]);
    }

    public function onCourseTaskPublish(Event $event)
    {
        $task = $event->getSubject();
        $this->syncTaskStatus($task, 'published');

        $this->dispatchEvent('course.task.publish.sync', new Event($task));
    }

    public function onCourseTaskUnpublish(Event $event)
    {
        $task = $event->getSubject();
        $this->syncTaskStatus($task, 'unpublished');
    }

    protected function syncTaskStatus($task, $status)
    {
        if ($task['copyId'] > 0) {
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $conditions = ['courseIds' => array_column($copiedCourses, 'id')];
        if (CourseType::DEFAULT === $course['courseType']) {
            $sameCategoryTasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
            $conditions['copyIds'] = array_column($sameCategoryTasks, 'id');
        } else {
            $conditions['copyId'] = $task['id'];
        }
        $this->getTaskDao()->update($conditions, ['status' => $status]);
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
        $this->getSchedulerService()->register([
            'name' => 'course_task_delete_sync_job_'.$task['id'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => time(),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Task\Job\CourseTaskDeleteSyncJob',
            'args' => ['taskId' => $task['id'], 'courseId' => $task['courseId']],
        ]);
    }

    private function syncForCreateTask($task, $syncCourses)
    {
        try {
            $activity = $this->getActivityDao()->get($task['activityId']);
            $syncActivities = $this->createSyncActivities($activity, $syncCourses);
            $this->createSyncMaterials($activity, $syncActivities);
            $this->createSyncTasks($task, $syncActivities);
            $this->getLogService()->info(LogModule::COURSE, 'sync_when_task_create', '课时同步创建成功', ['taskId' => $task['id']]);
        } catch (\Exception $e) {
            $this->getLogService()->error(LogModule::COURSE, 'sync_when_task_create', '课时同步创建失败', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function createSyncActivities($activity, $syncCourses)
    {
        $syncActivities = [];
        $syncActivity = ArrayToolkit::parts($activity, ['title', 'remark', 'mediaType', 'content', 'length', 'fromUserId', 'startTime', 'endTime', 'finishType', 'finishData']);
        $syncActivity['copyId'] = $activity['id'];
        $createdActivities = $this->getActivityDao()->findByCopyIdAndCourseIds($activity['id'], array_column($syncCourses, 'id'));
        $createdActivities = array_column($createdActivities, null, 'fromCourseId');
        foreach ($syncCourses as $syncCourse) {
            if (!empty($createdActivities[$syncCourse['id']])) {
                continue;
            }
            $syncActivity['fromCourseId'] = $syncCourse['id'];
            $syncActivity['fromCourseSetId'] = $syncCourse['courseSetId'];
            $ext = $this->getActivityConfig($activity['mediaType'])->copy($activity, [
                'refLiveroom' => 1, 'newActivity' => $syncActivity, 'isCopy' => 1, 'isSync' => 1,
            ]);
            if (!empty($ext)) {
                $syncActivity['mediaId'] = $ext['id'];
            }
            $syncActivities[] = $syncActivity;
            unset($syncActivity['mediaId']);
        }
        $this->getActivityDao()->batchCreate($syncActivities);

        return $this->getActivityDao()->findByCopyIdAndCourseIds($activity['id'], array_column($syncCourses, 'id'));
    }

    private function createSyncMaterials($activity, $syncActivities)
    {
        $materials = $this->getMaterialDao()->search(['lessonId' => $activity['id'], 'courseId' => $activity['fromCourseId']], [], 0, PHP_INT_MAX);
        if (empty($materials)) {
            return;
        }
        $syncMaterials = [];
        foreach ($materials as $material) {
            $syncMaterial = ArrayToolkit::parts($material, [
                'title',
                'description',
                'link',
                'fileId',
                'fileUri',
                'fileMime',
                'fileSize',
                'source',
                'userId',
                'type',
            ]);
            $syncMaterial['copyId'] = $material['id'];
            foreach ($syncActivities as $syncActivity) {
                $syncMaterial['courseSetId'] = $syncActivity['fromCourseSetId'];
                $syncMaterial['courseId'] = $syncActivity['fromCourseId'];
                $syncMaterial['lessonId'] = $syncActivity['id'];
                $syncMaterials[] = $syncMaterial;
            }
        }
        $this->getMaterialDao()->batchCreate($syncMaterials);
    }

    private function createSyncTasks($task, $syncActivities)
    {
        $syncCourseIds = array_column($syncActivities, 'fromCourseId');
        $createdTasks = $this->getTaskService()->findTasksByCopyIdAndLockedCourseIds($task['id'], $syncCourseIds);
        $createdTasks = array_column($createdTasks, null, 'courseId');
        $syncChapters = $this->getChapterDao()->findChaptersByCopyIdAndLockedCourseIds($task['categoryId'], $syncCourseIds);
        $syncChapters = array_column($syncChapters, null, 'courseId');
        $syncTasks = [];
        foreach ($syncActivities as $syncActivity) {
            if (!empty($createdTasks[$syncActivity['fromCourseId']])) {
                continue;
            }
            $syncTask = ArrayToolkit::parts($task, ['createdUserId', 'seq', 'title', 'isFree', 'isOptional', 'isLesson', 'startTime', 'endTime', 'number', 'mode', 'type', 'mediaSource', 'maxOnlineNum', 'status', 'length']);
            $syncTask['courseId'] = $syncActivity['fromCourseId'];
            $syncTask['fromCourseSetId'] = $syncActivity['fromCourseSetId'];
            $syncTask['activityId'] = $syncActivity['id'];
            $syncTask['categoryId'] = $syncChapters[$syncActivity['fromCourseId']]['id'];
            $syncTask['copyId'] = $task['id'];
            $syncTasks[] = $syncTask;
        }
        $this->getTaskDao()->batchCreate($syncTasks);
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

    protected function dispatchEvent($eventName, $subject, $arguments = [])
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
