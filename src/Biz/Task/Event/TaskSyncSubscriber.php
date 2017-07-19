<?php

namespace Biz\Task\Event;

use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;
use Biz\Course\Copy\Impl\ActivityTestpaperCopy;

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

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $ct) {
            $this->updateActivity($ct['activityId'], $ct['fromCourseSetId'], $ct['courseId']);

            $ct = $this->copyFields($task, $ct, array(
                'seq',
                'title',
                'isFree',
                'isOptional',
                'startTime',
                'endTime',
                'number',
                'mediaSource',
                'maxOnlineNum',
                'status',
            ));
            $this->getTaskDao()->update($ct['id'], $ct);
        }
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

    protected function updateMaterials($activity, $sourceActivity, $copiedTask)
    {
        $materials = $this->getMaterialDao()->search(array('lessonId' => $sourceActivity['id'], 'courseId' => $sourceActivity['fromCourseId']), array(), 0, PHP_INT_MAX);

        if (empty($materials)) {
            return;
        }

        $this->getMaterialDao()->deleteByLessonId($activity['id'], 'course');

        foreach ($materials as $material) {
            $newMaterial = $this->copyFields($material, array(), array(
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
            ));
            $newMaterial['copyId'] = $material['id'];
            $newMaterial['courseSetId'] = $copiedTask['fromCourseSetId'];
            $newMaterial['courseId'] = $copiedTask['courseId'];

            if ($material['lessonId'] > 0) {
                $newMaterial['lessonId'] = $activity['id'];
            }

            $this->getMaterialDao()->create($newMaterial);
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

    protected function updateActivity($activityId, $courseSetId, $courseId)
    {
        $activity = $this->getActivityDao()->get($activityId);
        $sourceActivity = $this->getActivityDao()->get($activity['copyId']);

        $testpaper = $this->syncTestpaper($sourceActivity, array('id' => $courseId, 'courseSetId' => $courseSetId));

        $activity = $this->copyFields($sourceActivity, $activity, array(
            'title',
            'remark',
            'content',
            'length',
            'startTime',
            'endTime',
        ));

        if (!empty($testpaper)) {
            $sourceActivity['testId'] = $testpaper['id'];
        }

        $ext = $this->getActivityConfig($activity['mediaType'])->sync($sourceActivity, $activity);

        if (!empty($ext)) {
            $activity['mediaId'] = $ext['id'];
        }

        $newActivity = $this->getActivityDao()->update($activity['id'], $activity);
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
}
