<?php

namespace Biz\Task\Job;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\AppLoggerConstant;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use Codeages\Biz\Framework\Event\Event;

class CourseTaskCreateSyncJob extends AbstractSyncJob
{
    public function execute()
    {
        $task = $this->getTaskService()->getTask($this->args['taskId']);
        try {
            $this->getLock()->get("sync_course_{$task['courseId']}", 10);
            $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
            $activity = $this->getActivityDao()->get($task['activityId']);
            $taskHelper = new BatchCreateHelper($this->getTaskDao());
            foreach ($copiedCourses as $cc) {
                $copiedTask = $this->getTaskService()->getCourseTaskByCourseIdAndCopyId($cc['id'], $task['id']);
                if (!empty($copiedTask)) {
                    continue;
                }
                $newActivity = $this->createActivity($activity, $cc);
                $newTask = [
                    'courseId' => $cc['id'],
                    'fromCourseSetId' => $cc['courseSetId'],
                    'createdUserId' => $task['createdUserId'],
                    'seq' => $task['seq'],
                    'categoryId' => $task['categoryId'],
                    'activityId' => $newActivity['id'],
                    'title' => $task['title'],
                    'isFree' => $task['isFree'],
                    'isOptional' => $task['isOptional'],
                    'isLesson' => $task['isLesson'],
                    'startTime' => $task['startTime'],
                    'endTime' => $task['endTime'],
                    'number' => $task['number'],
                    'mode' => $task['mode'],
                    'type' => $task['type'],
                    'mediaSource' => $task['mediaSource'],
                    'copyId' => $task['id'],
                    'maxOnlineNum' => $task['maxOnlineNum'],
                    'status' => $task['status'],
                    'length' => $task['length'],
                ];
                //if (!empty($task['mode'])) {
                $newChapter = $this->getChapterDao()->getByCopyIdAndLockedCourseId($task['categoryId'], $cc['id']);
                $newTask['categoryId'] = $newChapter['id'];
                //}
                $taskHelper->add($newTask);
            }
            $taskHelper->flush();
            $this->dispatchEvent('course.task.create.sync', new Event($task));
            $this->getLogService()->info(AppLoggerConstant::COURSE, 'sync_when_task_create', 'course.log.task.create.sync.success_tips', ['taskId' => $task['id']]);
            $this->getLock()->release("sync_course_{$task['courseId']}");
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::COURSE, 'sync_when_task_create', 'course.log.task.create.sync.fail_tips', ['error' => $e->getMessage()]);
            $this->innodbTrxLog($e);
            $this->getLock()->release("sync_course_{$task['courseId']}");
            if (!isset($this->args['repeat']) || $this->args['repeat'] < 5) {
                $this->getSchedulerService()->register(array(
                    'name' => 'course_task_create_sync_job_' . $task['id'],
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => time() + 120,
                    'misfire_policy' => 'executing',
                    'class' => 'Biz\Task\Job\CourseTaskCreateSyncJob',
                    'args' => array('taskId' => $task['id'], 'repeat' => (isset($this->args['repeat'])?$this->args['repeat']:0) + 1),
                ));
            }
            throw $e;
        }
    }

    private function createActivity($activity, $copiedCourse)
    {
        $newActivity = [
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
            'finishType' => $activity['finishType'],
            'finishData' => $activity['finishData'],
        ];
        $ext = $this->getActivityConfig($activity['mediaType'])->copy($activity, [
            'refLiveroom' => 1, 'newActivity' => $newActivity, 'isCopy' => 1, 'isSync' => 1,
        ]);
        if (!empty($ext)) {
            $newActivity['mediaId'] = $ext['id'];
        }
        $newActivity = $this->getActivityDao()->create($newActivity);
        //create materials if exists
        $this->createMaterials($newActivity, $activity, $copiedCourse);

        return $newActivity;
    }

    private function createMaterials($activity, $sourceActivity, $copiedCourse)
    {
        $materials = $this->getMaterialDao()->search(['lessonId' => $sourceActivity['id'], 'courseId' => $sourceActivity['fromCourseId']], [], 0, PHP_INT_MAX);
        if (empty($materials)) {
            return;
        }
        foreach ($materials as $material) {
            $newMaterial = $this->copyFields($material, [], [
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
            $newMaterial['copyId'] = $material['id'];
            $newMaterial['courseSetId'] = $copiedCourse['courseSetId'];
            $newMaterial['courseId'] = $copiedCourse['id'];
            if ($material['lessonId'] > 0) {
                $newMaterial['lessonId'] = $activity['id'];
            }
            $this->getMaterialDao()->create($newMaterial);
        }
    }

    protected function copyFields($source, $target, $fields)
    {
        if (empty($fields)) {
            return $target;
        }
        foreach ($fields as $field) {
            if (isset($source[$field])) {
                $target[$field] = $source[$field];
            }
        }

        return $target;
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return CourseChapterDao
     */
    private function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    /**
     * @return CourseMaterialDao
     */
    private function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
    }
}
