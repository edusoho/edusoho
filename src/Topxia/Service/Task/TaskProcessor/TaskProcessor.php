<?php
namespace Topxia\Service\Task\TaskProcessor;

interface TaskProcessor 
{
	public function getTask($taskId);

	public function addTask(array $fields);

	public function updateTask($taskId, array $fields);
    

}