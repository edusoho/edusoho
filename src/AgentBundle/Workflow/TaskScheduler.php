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
            $this->currentDateDuration = $dailyDuration;
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
            return $this->inputs['dailyLearnDuration'] * 3600;
        }
        $tasks = array_filter($this->tasks, function ($task) {
            return date('Y-m-d', $task['startTime']) < $this->inputs['endDate'];
        });
        $totalSeconds = array_sum(array_column($tasks, 'duration'));
        $studyDateCount = DateToolkit::countWeekdaysInDateRange($this->startDate, $this->inputs['endDate'], $this->inputs['weekDays']);

        return max(round($totalSeconds / $studyDateCount), 360);
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
            $task = $this->timeLimitTasks[$this->timeLimitTaskCursor];
            $this->scheduledTasks[] = [
                'id' => $task['id'],
                'courseId' => $this->inputs['courseId'],
                'title' => $task['title'],
                'date' => $this->dateCursor->format('Y-m-d'),
                'duration' => max($task['duration'], 60),
            ];
            $this->timeLimitTaskCursor++;
            $this->currentDateDuration -= $task['duration'];
        }
    }

    private function scheduleNoLimitTasks()
    {
        while ($this->shouldScheduleCurrentNoLimitTask()) {
            $task = $this->noLimitTasks[$this->noLimitTaskCursor];
            $duration = 'testpaper' == $task['type'] ? $task['duration'] : min($task['duration'], $this->currentDateDuration);
            $this->scheduledTasks[] = [
                'id' => $task['id'],
                'courseId' => $this->inputs['courseId'],
                'title' => $task['title'],
                'date' => $this->dateCursor->format('Y-m-d'),
                'duration' => max($duration, 60),
            ];
            if (($this->currentDateDuration < $task['duration']) && ('testpaper' != $task['type'])) {
                $this->noLimitTasks[$this->noLimitTaskCursor]['duration'] -= $this->currentDateDuration;
            } else {
                $this->noLimitTaskCursor++;
            }
            $this->currentDateDuration -= $task['duration'];
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
        if ($this->currentDateDuration < 1) {
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

        return $this->currentDateDuration > 59;
    }
}
