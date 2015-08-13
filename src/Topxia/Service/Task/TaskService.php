<?php

namespace Topxia\Service\Task;

interface TaskService
{
    public function getTask($id);

    public function addTask(array $fields);

    public function updateTask($id, array $fields);

    public function deleteTask($id);

    public function searchTasks($conditions, $orderBy, $start, $limit);

    public function searchTaskCount($conditions);
}