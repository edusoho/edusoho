<?php
namespace Topxia\Service\Task\TaskProcessor;

use Topxia\Service\Task\TaskProcessor\TaskProcessor;

class TaskProcessorFactory
{

	public static function create($target)
    {
    	if(empty($target) || !in_array($target,array('studyPlan'))) {
    		throw new Exception("用户导入类型不存在");
    	}

    	$class = __NAMESPACE__ . '\\' . ucfirst($target). 'TaskProcessor';

    	return new $class();
    }

}


