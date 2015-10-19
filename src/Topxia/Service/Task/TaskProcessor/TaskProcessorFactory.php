<?php
namespace Topxia\Service\Task\TaskProcessor;

use Topxia\Service\Task\TaskProcessor\TaskProcessor;

class TaskProcessorFactory
{

	public static function create($target)
    {
    	if(empty($target) || !in_array($target,array('studyPlan'))) {
    		throw new Exception("任务类型不存在不存在");
    	}

    	if ($target == 'studyPlan') {
    		$namespace = 'ClassroomPlan\Service\ClassroomPlan\TaskProcessor';
    	} else {
    		$namespace = __NAMESPACE__;
    	}

    	$class = $namespace . '\\' . ucfirst($target). 'TaskProcessor';

    	return new $class();
    }

}


