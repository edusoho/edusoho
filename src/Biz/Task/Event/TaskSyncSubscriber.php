<?php

namespace Biz\Task\Event;

use Biz\Task\Dao\TaskDao;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Strategy\CourseStrategy;
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
        $activity = $this->getActivityDao()->get($task['activityId']);
        foreach ($copiedCourses as $cc) {
            $newActivity = $this->createActivity($activity, $cc);

            $newTask = array(
                'courseId' => $cc['id'],
                'fromCourseSetId' => $cc['courseSetId'],
                'createdUserId' => $task['createdUserId'],
                'seq' => $task['seq'],
                'categoryId' => $task['categoryId'],
                'activityId' => $newActivity['id'],
                'title' => $task['title'],
                'isFree' => $task['isFree'],
                'isOptional' => $task['isOptional'],
                'startTime' => $task['startTime'],
                'endTime' => $task['endTime'],
                'number' => $task['number'],
                'mode' => $task['mode'],
                'type' => $task['type'],
                'mediaSource' => $task['mediaSource'],
                'copyId' => $task['id'],
                'maxOnlineNum' => $task['maxOnlineNum'],
                'status' => $task['status'],
            );

            if (!empty($task['mode'])) {
                $newChapter = $this->getChapterDao()->getByCopyIdAndLockedCourseId($task['categoryId'], $cc['id']);
                $newTask['categoryId'] = $newChapter['id'];
            }

            $this->getTaskDao()->create($newTask);
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
            if ($event->hasArgument('updateActivity') && $event->getArgument('updateActivity')) {
                $this->updateActivity($ct['activityId'], $ct['fromCourseSetId'], $ct['courseId']);
            }

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
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $ct) {
            if ($published && $ct['status'] !== 'published') {
                $this->getTaskService()->publishTask($ct['id']);
            } elseif (!$published && $ct['status'] === 'published') {
                $this->getTaskService()->unpublishTask($ct['id']);
            }
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

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedCourseMap = ArrayToolkit::index($copiedCourses, 'id');
        $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $ct) {
            $this->deleteTask($ct['id'], $copiedCourseMap[$ct['courseId']]);
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

    protected function createActivity($activity, $copiedCourse)
    {
        //create testpaper&questions if ref exists
        $testpaper = $this->syncTestpaper($activity, $copiedCourse);

        $testId = empty($testpaper) ? 0 : $testpaper['id'];

        $newActivity = array(
            'title' => $activity['title'],
            'remark' => $activity['remark'],
            'mediaType' => $activity['mediaType'],
            'content' => $activity['content'],
            'length' => $activity['length'],
            'fromCourseId' => $copiedCourse['id'],
            'fromCourseSetId' => $copiedCourse['courseSetId'],
            'fromUserId' => $activity['fromUserId'],
            'startTime' => $activity['startTime'],
            'endTime' => $activity['endTime'],
            'copyId' => $activity['id'],
        );

        $ext = $this->getActivityConfig($activity['mediaType'])->copy($activity, array(
            'testId' => $testId, 'refLiveroom' => 1, 'newActivity' => $newActivity,
        ));

        if (!empty($ext)) {
            $newActivity['mediaId'] = $ext['id'];
        }
        if ($newActivity['mediaType'] == 'homework' || $newActivity['mediaType'] == 'exercise') {
            $newActivity['mediaId'] = $testpaper['id'];
        }

        $newActivity = $this->getActivityDao()->create($newActivity);

        //create materials if exists
        $this->createMaterials($newActivity, $activity, $copiedCourse);

        return $newActivity;
    }

    protected function createMaterials($activity, $sourceActivity, $copiedCourse)
    {
        $materials = $this->getMaterialDao()->search(array('lessonId' => $sourceActivity['id'], 'courseId' => $sourceActivity['fromCourseId']), array(), 0, PHP_INT_MAX);

        if (empty($materials)) {
            return;
        }
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
            $newMaterial['courseSetId'] = $copiedCourse['courseSetId'];
            $newMaterial['courseId'] = $copiedCourse['id'];

            if ($material['lessonId'] > 0) {
                $newMaterial['lessonId'] = $activity['id'];
            }

            $this->getMaterialDao()->create($newMaterial);
        }
    }

    protected function syncTestpaper($activity, $copiedCourse)
    {
        $testpaperCopy = new ActivityTestpaperCopy($this->getBiz());

        return $testpaperCopy->copy($activity, array(
            'newCourseSetId' => $copiedCourse['courseSetId'],
            'newCourseId' => $copiedCourse['id'],
            'isCopy' => 1,
        ));
    }

    protected function updateActivity($activityId, $courseSetId, $courseId, $copiedTask)
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

        if ($activity['mediaType'] == 'homework' || $activity['mediaType'] == 'exercise') {
            $activity['mediaId'] = $testpaper['id'];
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
