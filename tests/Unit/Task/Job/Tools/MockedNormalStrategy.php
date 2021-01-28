<?php

namespace Tests\Unit\Task\Job\Tools;

class MockedNormalStrategy
{
    public function deleteTask($task)
    {
        if (empty($this->deletedTasks)) {
            $this->deletedTasks = array();
        }
        $this->deletedTasks[] = $task;
    }

    public function getDeletedTasks()
    {
        return $this->deletedTasks;
    }
}
