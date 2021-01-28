<?php

namespace AppBundle\Extensions\DataTag;

class TaskDataTag extends CourseBaseDataTag
{
    public function getData(array $arguments)
    {
        $this->checkArguments($arguments, array(
            'taskId',
        ));

        return $this->getTaskService()->getTask($arguments['taskId']);
    }
}
