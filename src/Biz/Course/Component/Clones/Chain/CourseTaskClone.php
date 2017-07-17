<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;

class CourseTaskClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseTasks($source, $options);
    }

    private function cloneCourseTasks($source, $options)
    {

    }

    protected function getFields()
    {
        // TODO: Implement getFields() method.
    }

    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }
}
