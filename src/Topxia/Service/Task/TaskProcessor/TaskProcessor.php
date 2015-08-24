<?php
namespace Topxia\Service\Task\TaskProcessor;

interface TaskProcessor 
{
	public function addTask(array $fields);

	public function finishTask(array $targetObject, $userId);
}