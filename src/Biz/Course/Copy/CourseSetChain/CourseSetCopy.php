<?php

namespace Biz\Course\Copy\CourseSetChain;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseSetDao;

class CourseSetCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = array())
    {
        return $this->doCopyCourseSet($source);
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
            'recommended',
            'recommendedSeq',
            'recommendedTime',
            'discountId',
            'discount',
            'orgId',
            'orgCode',
            'status',
            'teacherIds',
            ''
        );
    }

    // 和班级复制不同，课程复制需要的是完全一致，不需要有关联关系，所以只有用户会复制
    private function doCopyCourseSet($courseSet)
    {
        $newCourseSet = $this->filterFields($courseSet);

//        $newCourseSet['parentId'] = $courseSet['id'];
//        $newCourseSet['status'] = 'published';
        $newCourseSet['creator'] = $this->biz['user']['id'];
//        $newCourseSet['locked'] = 1; // 默认锁定

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
