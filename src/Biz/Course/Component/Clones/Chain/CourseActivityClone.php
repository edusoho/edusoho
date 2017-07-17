<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Component\Clones\AbstractClone;

class CourseActivityClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseActivity($source, $options);
    }

    protected function getFields()
    {
        // TODO: Implement getFields() method.
    }

    private function cloneCourseActivity($source, $options)
    {
        $newCourse = $options['newCourse'];
        $newCourseSet = $options['newCourseSet'];
        $activities = $this->getActivityDao()->findByCourseId($source['id']);
        if (empty($activities)) {
            return array();
        }
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
