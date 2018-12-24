<?php

namespace Biz\Course\Copy\Chain;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\CourseDao;

class CourseSetCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = array())
    {
        return $this->doCopyCourseSet($source, $config);
    }

    protected function getFields()
    {
        return array(
            'type',
            'title',
            'subtitle',
            'tags',
            'categoryId',
            'serializeMode',
            'summary',
            'goals',
            'audiences',
            'cover',
            'categoryId',
            'discountId',
            'discount',
            'orgId',
            'orgCode',
        );
    }

    private function doCopyCourseSet($courseSet, $config)
    {
        $newCourseSet = $this->filterFields($courseSet);

        $course = $this->getCourseDao()->get($config['courseId']);

        $newCourseSet['parentId'] = $courseSet['id'];
        $newCourseSet['status'] = 'published';
        $newCourseSet['creator'] = $this->biz['user']['id'];
        $newCourseSet['locked'] = 1; // 默认锁定
        $newCourseSet['maxCoursePrice'] = $course['originPrice'];
        $newCourseSet['minCoursePrice'] = $course['originPrice'];

        return $this->getCourseSetDao()->create($newCourseSet);
    }

    /**
     * @return CourseSetDao
     */
    private function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
