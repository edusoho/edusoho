<?php

namespace Biz\Task\Event;

use Biz\Task\Dao\TaskDao;
use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Task\Strategy\StrategyContext;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;
use Biz\Course\Copy\Impl\ActivityTestpaperCopy;

class TaskSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.create'    => 'onCourseTaskCreate',
            'course.task.update'    => 'onCourseTaskUpdate',
            'course.task.delete'    => 'onCourseTaskDelete',

            'course.task.publish'   => 'onCourseTaskUpdate',
            'course.task.unpublish' => 'onCourseTaskUpdate'
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
                'courseId'        => $cc['id'],
                'fromCourseSetId' => $cc['courseSetId'],
                'createdUserId'   => $task['createdUserId'],
                'seq'             => $task['seq'],
                'categoryId'      => $task['categoryId'],
                'activityId'      => $newActivity['id'],
                'title'           => $task['title'],
                'isFree'          => $task['isFree'],
                'isOptional'      => $task['isOptional'],
                'startTime'       => $task['startTime'],
                'endTime'         => $task['endTime'],
                'number'          => $task['number'],
                'mode'            => $task['mode'],
                'type'            => $task['type'],
                'mediaSource'     => $task['mediaSource'],
                'copyId'          => $task['id'],
                'maxOnlineNum'    => $task['maxOnlineNum'],
                'status'          => $task['status']
            );

            if (!empty($task['mode'])) {
                $newChapter            = $this->getChapterDao()->getByCopyIdAndLockedCourseId($task['categoryId'], $cc['id']);
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
        $copiedTasks     = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $ct) {
            $this->updateActivity($ct['activityId']);
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
                'status'
            ));

            $this->getTaskDao()->update($ct['id'], $ct);
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
        $copiedTasks     = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $ct) {
            $this->deleteTask($ct['id'], $copiedCourseMap[$ct['courseId']]);
        }
    }

    protected function createActivity($activity, $copiedCourse)
    {
        $ext         = $this->getActivityConfig($activity['mediaType'])->copy($activity, array());
        $newActivity = array(
            'title'           => $activity['title'],
            'remark'          => $activity['remark'],
            'mediaType'       => $activity['mediaType'],
            'content'         => $activity['content'],
            'length'          => $activity['length'],
            'fromCourseId'    => $copiedCourse['id'],
            'fromCourseSetId' => $copiedCourse['courseSetId'],
            'fromUserId'      => $activity['fromUserId'],
            'startTime'       => $activity['startTime'],
            'endTime'         => $activity['endTime'],
            'copyId'          => $activity['id']
        );
        if (!empty($ext)) {
            $newActivity['mediaId'] = $ext['id'];
        }
        $newActivity = $this->getActivityDao()->create($newActivity);
        //create materials if exists
        $this->createMaterials($newActivity, $activity, $copiedCourse);
        //create testpaper&questions if ref exists
        $this->createTestpapers($newActivity, $activity, $copiedCourses);
        return $newActivity;
    }

    protected function createMaterials($activity, $sourceActivity, $copiedCourse)
    {
        $materials = $this->getMaterialDao()->search(array('lessonId' => $sourceActivity['id'], 'courseId' => $sourceActivity['fromCourseId']), array(), 0, PHP_INT_MAX);
        $this->getLogService()->info('TaskSync', 'createMaterials', count($materials));
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
                'type'
            ));
            $newMaterial['copyId']      = $material['id'];
            $newMaterial['courseSetId'] = $copiedCourse['courseSetId'];
            $newMaterial['courseId']    = $copiedCourse['id'];

            if ($material['lessonId'] > 0) {
                $newMaterial['lessonId'] = $activity['id'];
            }

            $this->getMaterialDao()->create($newMaterial);
        }
    }

    protected function createTestpapers($newActivity, $activity, $copiedCourses)
    {
        $testpaperCopy = new ActivityTestpaperCopy($this->getBiz());
        $testpaperCopy->copy($activity, array('newActivity' => $newActivity, 'isCopy' => 1));
    }

    protected function updateActivity($activityId)
    {
        $activity       = $this->getActivityDao()->get($activityId);
        $sourceActivity = $this->getActivityDao()->get($activity['copyId']);
        $activity       = $this->copyFields($sourceActivity, $activity, array(
            'title',
            'remark',
            'content',
            'length',
            'startTime',
            'endTime'
        ));
        $this->getActivityDao()->update($activity['id'], $activity);

        $this->getActivityConfig($activity['mediaType'])->sync($sourceActivity, $activity);
    }

    protected function deleteTask($taskId, $course)
    {
        $strategy = StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->getBiz());
        //delete task and belongings
        $strategy->deleteTask($taskId);
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
     * @return Activity
     */
    protected function getActivityConfig($type)
    {
        $biz = $this->getBiz();
        return $biz["activity_type.{$type}"];
    }
}
