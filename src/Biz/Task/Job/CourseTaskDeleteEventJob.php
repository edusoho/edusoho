<?php

namespace Biz\Task\Job;

use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Constant\LogModule;
use Codeages\Biz\Framework\Event\Event;

class CourseTaskDeleteEventJob extends AbstractSyncJob
{
    public function execute()
    {
        $tasks = $this->args['tasks'];
        try {
            foreach ($tasks as $key => $task) {
                $this->dispatchEvent('course.task.delete', new Event($task, ['user' => $this->args['user']]));
                unset($tasks[$key]);
            }
            $this->getLogService()->info(LogModule::COURSE, 'task_delete_event', '执行删除课时任务订阅事件成功', ['tasks' => $this->args['tasks']]);
        } catch (\Exception $e) {
            $this->getLogService()->error(LogModule::COURSE, 'task_delete_event', '执行删除课时任务订阅事件失败', ['tasks' => $tasks, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->innodbTrxLog($e);
            $retry = $this->args['retry'] ?? 0;
            if ($retry < 5) {
                $tasks = array_values($tasks);
                $this->getSchedulerService()->register([
                    'name' => "task_delete_event_job_{$tasks[0]['id']}",
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => time() + 120 * $retry,
                    'misfire_policy' => 'executing',
                    'class' => 'Biz\Task\Job\CourseTaskDeleteEventJob',
                    'args' => ['tasks' => $tasks, 'retry' => count($tasks) < count($this->args['tasks']) ? $retry : $retry + 1],
                ]);
            }
            throw $e;
        }
    }
}
