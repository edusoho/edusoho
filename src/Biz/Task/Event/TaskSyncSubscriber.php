<?php
/**
 * Created by PhpStorm.
 * User: malianbo
 * Date: 17/2/5
 * Time: 16:31
 */

namespace Biz\Task\Event;

use Biz\Activity\Config\Activity;
use Biz\Task\Strategy\StrategyContext;
use Biz\Task\Dao\TaskDao;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;
use Topxia\Common\ArrayToolkit;

class TaskSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.create'     => 'onCourseTaskCreate',
            'course.task.update'     => 'onCourseTaskUpdate',
            'course.task.delete'     => 'onCourseTaskDelete'

//            'course.activity.create' => 'onCourseActivityCreate',
//            'course.activity.update' => 'onCourseActivityUpdate',
//            'course.activity.delete' => 'onCourseActivityDelete'
        );
    }


    public function onCourseTaskCreate(Event $event)
    {
        $task = $event->getSubject();
        if($task['copyId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }
        $activity = $this->getActivityDao()->get($task['activityId']);
        foreach ($copiedCourses as $cc){
            $newActivity = $this->createActivity($activity, $cc);

            $newTask = array(
                'courseId' => $cc['id'],
                'fromCourseSetId' => $cc['courseSetId'],
                'seq' => $task['seq'],
                'categoryId' => $task['categoryId'],
                'activityId' => $newActivity['id'],
                'title' => $task['title'],
                'isFree' => $task['isFree'],
                'isOptional' => $task['isOptional'],
                'startTime' => $task['startTime'],
                'endTime' => $task['endTime'],
                'number' => $task['number'],
                'mediaSource' => $task['mediaSource'],
                'copyId' => $task['id'],
                'maxOnlineNum' => $task['maxOnlineNum']
            );

            if(!empty($task['mode'])){
                $newChapter = $this->getChapterDao()->getByCopyIdAndLockedCourseId($task['categoryId'], $cc['id']);
                $newTask['categoryId'] = $newChapter['id'];
            }

            $this->getTaskDao()->create($newTask);
        }
    }

    public function onCourseTaskUpdate(Event $event)
    {
        $task = $event->getSubject();
        if($task['copyId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $ct) {
            $this->updateActivity($task['activityId']);
            $ct = $this->copyFields($task, $ct, array(
                'seq' => $task['seq'],
                'title',
                'isFree',
                'isOptional',
                'startTime',
                'endTime',
                'number',
                'mediaSource',
                'maxOnlineNum'
            ));

            $this->getTaskDao()->update($ct['id'], $ct);
        }
    }

    public function onCourseTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        if($task['copyId'] > 0){
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if(empty($copiedCourses)){
            return;
        }

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedCourseMap = ArrayToolkit::index($copiedCourses, 'id');
        $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $ct) {
            $this->deleteTask($ct['id'], $copiedCourseMap[$ct['courseId']]);
        }
    }

//    public function onCourseActivityCreate(Event $event)
//    {
//
//    }
//
//    public function onCourseActivityUpdate(Event $event)
//    {
//
//    }
//
//    public function onCourseActivityDelete(Event $event)
//    {
//
//    }

    protected function createActivity($activity, $copiedCourse)
    {
        $ext = $this->getActivityConfig($activity['mediaType'])->copy($activity, array());
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
            'copyId' => $activity['id']
        );
        if(!empty($ext)){
            $newActivity['mediaId'] = $ext['id'];
        }
        return $this->getActivityDao()->create($newActivity);
    }

    protected function updateActivity($activityId)
    {
        $activity = $this->getActivityDao()->get($activityId);
        $sourceActivity = $this->getActivityDao()->get($activity['copyId']);
        $this->copyFields($sourceActivity, $activity, array(
            'title',
            'remark',
            'content',
            'length',
            'startTime',
            'endTime'
        ));
        $this->getActivityDao()->update($activity['id'], $activity);
        $this->getActivityConfig($activity['mediaType'])->sync($activity);
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
     * @param $type
     * @return Activity
     */
    protected function getActivityConfig($type)
    {
        $biz = $this->getBiz();
        return $biz["activity_type.{$type}"];
    }
}