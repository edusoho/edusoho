<?php

namespace AgentBundle\Workflow;

use AppBundle\Common\DateToolkit;
use DateTime;

class TaskScheduler
{
    private $inputs;

    private $tasks;

    private $timeLimitTasks;

    private $noLimitTasks;

    private $startDate;

    private $dateCursor;

    private $timeLimitTaskCursor;

    private $noLimitTaskCursor;

    private $currentDateDuration;

    private $scheduledTasks;

    public function schedule($inputs, $tasks)
    {
        $this->inputs = $inputs;
        $this->tasks = $tasks;
        $this->initStartDate();

        return $this->scheduleTasks();
    }

    private function initStartDate()
    {
        $this->startDate = empty($this->inputs['startDate']) ? date('Y-m-d') : $this->inputs['startDate'];
    }

    private function scheduleTasks()
    {
        $this->separateTasks();
        $this->timeLimitTaskCursor = 0;
        $this->noLimitTaskCursor = 0;
        $this->scheduledTasks = [];
        $this->dateCursor = new DateTime($this->startDate);
        $dailyDuration = $this->calculateDailyDuration();
        while (!$this->isFinished()) {
            $this->currentDateDuration = $dailyDuration * 3600;
            $this->scheduleTimeLimitTasks();
            $this->scheduleNoLimitTasks();
            $this->dateCursor->modify('+1 day');
        }

        return $this->scheduledTasks;
    }

    private function separateTasks()
    {
        $this->timeLimitTasks = [];
        $this->noLimitTasks = [];
        foreach ($this->tasks as $task) {
            if ($task['startTime'] > 0) {
                $this->timeLimitTasks[] = $task;
            } else {
                $this->noLimitTasks[] = $task;
            }
        }
        $this->sortTimeLimitTasks();
    }

    private function sortTimeLimitTasks()
    {
        usort($this->timeLimitTasks, function ($first, $second) {
            if (date('Y-m-d', $first['endTime']) == date('Y-m-d', $second['endTime'])) {
                return date('Y-m-d', $first['startTime']) <=> date('Y-m-d', $second['startTime']);
            }
            if (empty($first['endTime'])) {
                return 1;
            }
            if (empty($second['endTime'])) {
                return -1;
            }

            return date('Y-m-d', $first['endTime']) <=> date('Y-m-d', $second['endTime']);
        });
    }

    private function calculateDailyDuration()
    {
        if (empty($this->inputs['endDate'])) {
            return $this->inputs['dailyLearnDuration'];
        }
        $tasks = array_filter($this->tasks, function ($task) {
            return date('Y-m-d', $task['startTime']) < $this->inputs['endDate'];
        });
        $totalSeconds = array_sum(array_column($tasks, 'duration'));
        $studyDateCount = DateToolkit::countWeekdaysInDateRange($this->startDate, $this->inputs['endDate'], $this->inputs['weekDays']);

        return $this->formatDuration($totalSeconds / $studyDateCount);
    }

    private function isFinished()
    {
        if ($this->timeLimitTaskCursor < count($this->timeLimitTasks)) {
            return false;
        }
        if ($this->noLimitTaskCursor < count($this->noLimitTasks)) {
            return false;
        }

        return true;
    }

    private function scheduleTimeLimitTasks()
    {
        while ($this->shouldScheduleCurrentTimeLimitTask()) {
            $taskDuration = $this->timeLimitTasks[$this->timeLimitTaskCursor]['duration'];
            $this->scheduledTasks[] = [
                'id' => $this->timeLimitTasks[$this->timeLimitTaskCursor]['id'],
                'courseId' => $this->inputs['courseId'],
                'title' => $this->timeLimitTasks[$this->timeLimitTaskCursor]['title'],
                'date' => $this->dateCursor->format('Y-m-d'),
                'duration' => $this->formatDuration($taskDuration),
            ];
            $this->timeLimitTaskCursor++;
            $this->currentDateDuration = $this->currentDateDuration - $taskDuration;
        }
    }

    private function scheduleNoLimitTasks()
    {
        while ($this->shouldScheduleCurrentNoLimitTask()) {
            $taskDuration = $this->noLimitTasks[$this->noLimitTaskCursor]['duration'];
            $this->scheduledTasks[] = [
                'id' => $this->noLimitTasks[$this->noLimitTaskCursor]['id'],
                'courseId' => $this->inputs['courseId'],
                'title' => $this->noLimitTasks[$this->noLimitTaskCursor]['title'],
                'date' => $this->dateCursor->format('Y-m-d'),
                'duration' => min($this->formatDuration($taskDuration), $this->formatDuration($this->currentDateDuration)),
            ];
            if ($this->currentDateDuration < $taskDuration) {
                $this->noLimitTasks[$this->noLimitTaskCursor]['duration'] -= $this->currentDateDuration;
                $this->currentDateDuration = 0;
            } else {
                $this->currentDateDuration -= $taskDuration;
                $this->noLimitTaskCursor++;
            }
        }
    }

    private function shouldScheduleCurrentTimeLimitTask()
    {
        if (count($this->timeLimitTasks) == $this->timeLimitTaskCursor) {
            return false;
        }
        if (date('Y-m-d', $this->timeLimitTasks[$this->timeLimitTaskCursor]['startTime']) > $this->dateCursor->format('Y-m-d')) {
            return false;
        }
        if ($this->dateCursor->format('Y-m-d') == date('Y-m-d', $this->timeLimitTasks[$this->timeLimitTaskCursor]['endTime'])) {
            return true;
        }
        if ($this->currentDateDuration <= 0) {
            return false;
        }
        if (in_array($this->dateCursor->format('N'), $this->inputs['weekDays'])) {
            return true;
        }

        return false;
    }

    private function shouldScheduleCurrentNoLimitTask()
    {
        if (!in_array($this->dateCursor->format('N'), $this->inputs['weekDays'])) {
            return false;
        }
        if (count($this->noLimitTasks) == $this->noLimitTaskCursor) {
            return false;
        }

        return $this->currentDateDuration > 0;
    }

    private function formatDuration($seconds)
    {
        return max(round($seconds / 3600, 1), 0.1);
    }
}
