<?php

namespace Biz\Task\Job;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\AppLoggerConstant;
use Biz\Course\Copy\Chain\ActivityTestpaperCopy;
use Biz\Course\Dao\CourseDao;
use Biz\System\Service\LogService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CourseTaskUpdateSyncJob extends AbstractJob
{
    public function execute()
    {
        try {
            $task = $this->getTaskService()->getTask($this->args['taskId']);
            $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);

            $copiedCourseIds = array_column($copiedCourses, 'id');
            $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);

            $helper = new BatchUpdateHelper($this->getTaskDao());
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
                    'length',
                ));

                $helper->add('id', $ct['id'], $ct);
            }

            $helper->flush();

            $this->getLogService()->info(AppLoggerConstant::COURSE, 'sync_when_task_update', 'course.log.task.update.sync.success_tips', array('taskId' => $task['id']));
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::COURSE, 'sync_when_task_update', 'course.log.task.update.sync.fail_tips', array('error' => $e->getMessage()));
        }
    }

    private function updateActivity($activityId, $courseSetId, $courseId)
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
            'finishType',
            'finishData',
        ));

        if (!empty($testpaper)) {
            $sourceActivity['testId'] = $testpaper['id'];
        }

        $ext = $this->getActivityConfig($activity['mediaType'])->sync($sourceActivity, $activity);

        if (!empty($ext)) {
            $activity['mediaId'] = $ext['id'];
        }

        $this->getActivityDao()->update($activity['id'], $activity);
    }

    private function syncTestpaper($activity, $copiedCourse)
    {
        if ('testpaper' != $activity['mediaType']) {
            return array();
        }

        $testpaperCopy = new ActivityTestpaperCopy($this->biz);

        return $testpaperCopy->copy($activity, array(
            'newCourseSetId' => $copiedCourse['courseSetId'],
            'newCourseId' => $copiedCourse['id'],
            'isCopy' => 1,
        ));
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
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
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
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
