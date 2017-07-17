<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Task\Dao\TaskDao;

class CourseTaskClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseTasks($source, $options);
    }

    private function cloneCourseTasks($source, $options)
    {
        $user = $this->biz['user'];
        $tasks = $this->getTaskDao()->findByCourseId($source['id']);

        $chaptersClone = $this->cloneCourseChapters($source,$options);

        if(empty($tasks)) {
            return array();
        }
    }

    private function cloneCourseChapters($source,$options)
    {
        $cloneChapters = new CourseChapterClone($this->biz);
        return $cloneChapters->clones($source,$options);

    }

    protected function getFields()
    {
        // TODO: Implement getFields() method.
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }
}
