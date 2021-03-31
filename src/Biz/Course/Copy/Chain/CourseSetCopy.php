<?php

namespace Biz\Course\Copy\Chain;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseSetDao;

class CourseSetCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = [])
    {
        return $this->doCopyCourseSet($source);
    }

    protected function getFields()
    {
        return [
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
        ];
    }

    private function doCopyCourseSet($courseSet)
    {
        $newCourseSet = $this->filterFields($courseSet);

        $newCourseSet['parentId'] = $courseSet['id'];
        $newCourseSet['status'] = 'published';
        $newCourseSet['creator'] = $this->biz['user']['id'];
        $newCourseSet['locked'] = 1; // 默认锁定
        $newCourseSet['discountId'] = 0; // 商品打折不影响班级内的课程
        $newCourseSet['isClassroomRef'] = 1;

        return $this->getCourseSetDao()->create($newCourseSet);
    }

    /**
     * @return CourseSetDao
     */
    private function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }
}
