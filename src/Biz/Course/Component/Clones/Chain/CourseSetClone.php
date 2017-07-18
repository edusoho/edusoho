<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Course\Dao\CourseSetDao;

class CourseSetClone extends AbstractClone
{
    protected function cloneEntity($source, $options = array())
    {
        $params = $options['params'];

        return $this->doCopyCourseSet($source, $params);
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
            'teacherIds',
            'materialNum',
        );
    }

    // 和班级复制不同，课程复制需要的是完全一致，不需要有关联关系，所以只有用户会复制
    private function doCopyCourseSet($courseSet, $params)
    {
        $newCourseSet = $this->filterFields($courseSet);
        $newCourseSet = array_merge($newCourseSet, $params);

        $newCourseSet['creator'] = $this->biz['user']['id'];
        $newCourseSet['status'] = 'draft';

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
