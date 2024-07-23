<?php

namespace Biz\Course\Job;

use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Topxia\Service\Common\ServiceKernel;

class CourseTaskJobLogJob extends AbstractJob
{
    /**
     * upgrade-23.3.9升级脚本用到的job
     */
    public function execute()
    {
        $start = $this->args['start'] ?? 0;

        $jobLogs = $this->getJobLogDao()->search(
            ['name' => 'course_task_create_sync_job_', 'status' => 'error'],
            ['id' => 'asc'],
            $start,
            1,
            ['id', 'args']
        );
        if ($jobLogs) {
            $taskId = $jobLogs[0]['args']['taskId'];
            $task = $this->getTaskService()->getTask($taskId);
            if (!empty($task)) {
                $courseId = $task['courseId'];
                $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($courseId, 1);
                foreach ($copiedCourses as $copiedCourse) {
                    $this->getTaskService()->syncClassroomCourseTasks($copiedCourse['id'], true);
                }
            }

            ++$start;
            $this->createCourseTaskJobLogJob($start);
        }
    }

    public function createCourseTaskJobLogJob(int $start)
    {
        $this->getSchedulerService()->register([
            'name' => 'CourseTaskJobLogJob_'.$start,
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => time() + 3,
            'class' => 'Biz\Course\Job\CourseTaskJobLogJob',
            'args' => ['start' => $start],
            'misfire_threshold' => 10 * 60,
        ]);
    }

    protected function getSchedulerService()
    {
        return ServiceKernel::instance()->createService('Scheduler:SchedulerService');
    }

    public function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getJobLogDao()
    {
        return $this->biz->dao('Scheduler:JobLogDao');
    }

    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
