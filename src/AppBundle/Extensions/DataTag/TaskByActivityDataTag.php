<?php

namespace AppBundle\Extensions\DataTag;

class TaskByActivityDataTag extends CourseBaseDataTag
{
    /**
     * @param array $arguments
     *
     * @return array
     */
    public function getData(array $arguments)
    {
        $this->checkArguments($arguments, array(
            'courseId',
            'activityId',
        ));

        return $this->getTaskService()->getTaskByCourseIdAndActivityId($arguments['courseId'], $arguments['activityId']);
    }
}
