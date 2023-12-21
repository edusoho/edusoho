<?php

namespace Biz\Task\Job;

use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Constant\LogModule;
use Codeages\Biz\Framework\Event\Event;

class CourseTaskCreateSyncJob extends AbstractSyncJob
{
    public function execute()
    {
        $task = $this->getTaskService()->getTask($this->args['taskId']);
        if (empty($task)) {
            return;
        }
        try {
            $this->dispatchEvent('course.task.create.sync', new Event($task));
            $this->getLogService()->info(LogModule::COURSE, 'async_when_task_create', 'course.log.task.create.sync.success_tips', ['taskId' => $task['id']]);
        } catch (\Exception $e) {
            $this->getLogService()->error(LogModule::COURSE, 'async_when_task_create', 'course.log.task.create.sync.fail_tips', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->innodbTrxLog($e);
            $retry = $this->args['retry'] ?? 0;
            if ($retry < 5) {
                $this->getSchedulerService()->register([
                    'name' => 'course_task_create_sync_job_'.$task['id'],
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => time() + 120 * $retry,
                    'misfire_policy' => 'executing',
                    'class' => 'Biz\Task\Job\CourseTaskCreateSyncJob',
                    'args' => ['taskId' => $task['id'], 'retry' => $retry + 1],
                ]);
            }
            throw $e;
        }
    }
}
