<?php

namespace Biz\Task\Job;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\AppLoggerConstant;
use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Event\Event;

class CourseTaskUpdateSyncJob extends AbstractSyncJob
{
    public function execute()
    {
        $task = $this->getTaskService()->getTask($this->args['taskId']);
        try {
            $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
            $copiedCourseIds = array_column($copiedCourses, 'id');
            $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
            $helper = new BatchUpdateHelper($this->getTaskDao());
            foreach ($copiedTasks as $ct) {
                $this->updateActivity($ct['activityId']);
                $ct = $this->copyFields($task, $ct, array(
                    'seq',
                    'title',
                    'isFree',
                    'isOptional',
                    'isLesson',
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
            $this->dispatchEvent('course.task.update.sync', new Event($task));
            $this->getLogService()->info(AppLoggerConstant::COURSE, 'sync_when_task_update', 'course.log.task.update.sync.success_tips', array('taskId' => $task['id']));
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::COURSE, 'sync_when_task_update', 'course.log.task.update.sync.fail_tips', array('error' => $e->getMessage()));
            $this->innodbTrxLog($e);
            if (!isset($this->args['repeat']) || $this->args['repeat'] < 5) {
                $this->getSchedulerService()->register(array(
                    'name' => 'course_task_update_sync_job_' . $task['id'],
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => time() + 120,
                    'misfire_policy' => 'executing',
                    'class' => 'Biz\Task\Job\CourseTaskUpdateSyncJob',
                    'args' => array('taskId' => $task['id'], 'repeat' => (isset($this->args['repeat'])?$this->args['repeat']:0) + 1),
                ));
            }
            throw $e;
        }
    }

    private function updateActivity($activityId)
    {
        $activity = $this->getActivityDao()->get($activityId);
        $sourceActivity = $this->getActivityDao()->get($activity['copyId']);
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
        $ext = $this->getActivityConfig($activity['mediaType'])->sync($sourceActivity, $activity);
        if (!empty($ext)) {
            $activity['mediaId'] = $ext['id'];
        }
        $this->getActivityDao()->update($activity['id'], $activity);
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
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
